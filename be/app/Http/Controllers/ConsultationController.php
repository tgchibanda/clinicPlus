<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\MedicalHistory;
use App\Models\Patient;   // adjust if your model/table differs
use App\Models\User;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ConsultationController extends Controller
{
    /**
     * Create a booking (consultation).
     * - user_id: who is creating the booking
     * - doctor_id: which doctor is assigned
     * - location_id: where the consult happens
     * - start_at / end_at: must be 30-min aligned
     * - For super doctors (users.is_super_doctor = true), all bookings
     *   on the same day must be at a single location.
     */
    public function store(Request $request)
{
    Log::debug(__METHOD__.' request', $request->all());

    $validated = $request->validate([
        'user_id'       => ['required', 'integer', 'exists:users,id'],      // creator
        'doctor_id'     => ['required', 'integer', 'exists:users,id'],      // assigned doctor
        'patient_id'    => ['required', 'integer', 'exists:patients,id'],
        'location_id'   => ['required', 'integer', 'exists:locations,id'],
        'reason'        => ['required', 'string'],
        'instruction'   => ['nullable', 'string'],
        'past_medical_history' => ['nullable', 'string'],
        'start_at'      => ['required', 'date'],
        'end_at'        => ['required', 'date'],
        'consultation_fee' => ['nullable', 'numeric', 'min:0'],
        'payment_method' => 'required',
    ]);

    $start = \Carbon\Carbon::parse($validated['start_at'])->seconds(0);
    $end   = \Carbon\Carbon::parse($validated['end_at'])->seconds(0);

    if (!in_array($start->minute, [0,30], true) || !in_array($end->minute, [0,30], true)) {
        return response()->json([
            'success' => false,
            'message' => 'Start and end times must be on 30-minute boundaries (e.g., 09:00, 09:30, 10:00).',
        ], 422);
    }

    $doctor   = \App\Models\User::findOrFail($validated['doctor_id']);
    $location = \App\Models\Location::findOrFail($validated['location_id']);

    // Overlap check for doctor
    $overlap = \App\Models\Consultation::where('doctor_id', $doctor->id)
        ->where(function ($q) use ($start, $end) {
            $q->whereBetween('start_at', [$start, $end])
              ->orWhereBetween('end_at', [$start, $end])
              ->orWhere(function ($qq) use ($start, $end) {
                  $qq->where('start_at', '<=', $start)
                     ->where('end_at', '>=', $end);
              });
        })
        ->exists();

    if ($overlap) {
        return response()->json([
            'success' => false,
            'message' => 'Selected time overlaps with an existing booking for this doctor.',
        ], 422);
    }

    // Super doctor: single location per day
    if ((bool)($doctor->is_super_doctor ?? false)) {
        $dayStart = $start->copy()->startOfDay();
        $dayEnd   = $start->copy()->endOfDay();

        $diffLocation = \App\Models\Consultation::where('doctor_id', $doctor->id)
            ->whereBetween('start_at', [$dayStart, $dayEnd])
            ->where('location_id', '!=', $location->id)
            ->exists();

        if ($diffLocation) {
            return response()->json([
                'success' => false,
                'message' => 'This super doctor already has bookings at a different location on this day.',
            ], 422);
        }
    }

    // Create booking + optional medical history
    $booking = \DB::transaction(function () use ($validated, $start, $end) {
        $booking = \App\Models\Consultation::create([
            'user_id'     => $validated['user_id'],     // creator
            'doctor_id'   => $validated['doctor_id'],   // assigned doctor
            'patient_id'  => $validated['patient_id'],
            'location_id' => $validated['location_id'],
            'reason'      => $validated['reason'],
            'instruction' => $validated['instruction'] ?? null,
            'start_at'    => $start,
            'end_at'      => $end,
            'status'      => 0, // pending
            'consultation_fee' => $validated['consultation_fee'] ?? null,
            'payment_method' => $request['payment_method']
        ]);

        if (!empty($validated['past_medical_history'])) {
            \App\Models\MedicalHistory::create([
                'consultation_id' => $booking->id, // <-- use $booking, not $consultation
                'history'         => $validated['past_medical_history'],
            ]);
        }

         // Update patient status to 'booked'
        $patient = \App\Models\Patient::find($validated['patient_id']);
        if ($patient) {
            $patient->status = 'booked';
            $patient->assigned_doctor_id = $validated['doctor_id'];
            $patient->save();
        }

        return $booking;
    });

    return response()->json([
        'success' => true,
        'message' => 'Consultation booked successfully.',
        'data'    => $booking->load(['patient', 'doctor', 'location', 'creator']),
    ], 201);
}


    /**
     * Save/update doctor notes.
     */
    public function doctorNotes(Request $request)
    {
        Log::debug(__METHOD__.' request', $request->all());

        $validated = $request->validate([
            'consultation_id' => ['required', 'integer', 'exists:consultations,id'],
            'examination'     => ['required', 'string'],
            'management'      => ['nullable', 'string'],
            'diagnosis'       => ['nullable', 'string'],
            'investigation'   => ['nullable', 'string'],
            'request_forms'   => ['nullable', 'string'],
        ]);

        $consultation = Consultation::findOrFail($validated['consultation_id']);

        $consultation->fill([
            'examination'   => $validated['examination'],
            'management'    => $validated['management']    ?? $consultation->management,
            'diagnosis'     => $validated['diagnosis']     ?? $consultation->diagnosis,
            'investigation' => $validated['investigation'] ?? $consultation->investigation,
            'request_forms' => $validated['request_forms'] ?? $consultation->request_forms,
            'status'        => 4, // e.g., notes recorded
        ])->save();

        return response()->json([
            'success' => true,
            'message' => 'Doctor notes saved successfully.',
            'data'    => $consultation->fresh(),
        ], 200);
    }

    /**
     * Return available 30-minute slots for a doctor on date+location.
     * GET /api/consultations/available-slots?doctor_id=..&location_id=..&date=YYYY-MM-DD[&start_hour=9&end_hour=17]
     */
    public function availableSlots(Request $request)
    {
        $validated = $request->validate([
            'doctor_id'   => ['required', 'integer', 'exists:users,id'],
            'location_id' => ['required', 'integer', 'exists:locations,id'],
            'date'        => ['required', 'date_format:Y-m-d'],
            'start_hour'  => ['nullable', 'integer', 'between:0,23'],
            'end_hour'    => ['nullable', 'integer', 'between:0,23'],
        ]);

        $doctor   = User::findOrFail($validated['doctor_id']);
        $location = Location::findOrFail($validated['location_id']);

        $dayStart = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.sprintf('%02d:00', $validated['start_hour'] ?? 9))->seconds(0);
        $dayEnd   = Carbon::createFromFormat('Y-m-d H:i', $validated['date'].' '.sprintf('%02d:00', $validated['end_hour']   ?? 17))->seconds(0);

        // Build 30-min slots
        $slots = [];
        $cursor = $dayStart->copy();
        while ($cursor->lt($dayEnd)) {
            $s = $cursor->copy();
            $e = $cursor->copy()->addMinutes(30);
            if ($e->gt($dayEnd)) { break; }
            $slots[] = ['start_at' => $s->toIso8601String(), 'end_at' => $e->toIso8601String()];
            $cursor->addMinutes(30);
        }

        // Fetch existing bookings for this doctor on that day
        $bookings = Consultation::where('doctor_id', $doctor->id)
            ->whereBetween('start_at', [$dayStart, $dayEnd])
            ->get(['start_at', 'end_at', 'location_id']);

        // Super doctor: if booked anywhere that day, restrict to that location
        if ($this->isSuperDoctor($doctor)) {
            $dayLocationIds = $bookings->pluck('location_id')->filter()->unique()->values();
            if ($dayLocationIds->count() > 0) {
                $lockedLocationId = $dayLocationIds[0];
                if ((int)$lockedLocationId !== (int)$location->id) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Doctor is already booked at another location today.',
                        'data'    => [
                            'date'        => $validated['date'],
                            'doctor_id'   => $doctor->id,
                            'location_id' => $location->id,
                            'slots'       => [], // none available at this location today
                            'restricted_to_location_id' => (int)$lockedLocationId,
                        ],
                    ], 200);
                }
            }
        }

        // Mark availability (no overlap)
        $available = array_map(function ($slot) use ($bookings) {
            $s = Carbon::parse($slot['start_at']);
            $e = Carbon::parse($slot['end_at']);

            $conflict = $bookings->first(function ($b) use ($s, $e) {
                $bs = Carbon::parse($b->start_at);
                $be = Carbon::parse($b->end_at);
                return $s->lt($be) && $e->gt($bs);
            });

            return [
                'start_at'  => $slot['start_at'],
                'end_at'    => $slot['end_at'],
                'available' => $conflict ? false : true,
            ];
        }, $slots);

        return response()->json([
            'success' => true,
            'message' => 'Available slots fetched.',
            'data'    => [
                'date'        => $validated['date'],
                'doctor_id'   => $doctor->id,
                'location_id' => $location->id,
                'slots'       => $available,
            ],
        ], 200);
    }

    /* ===========================
       Helpers
       =========================== */

    private function isThirtyMinuteAligned(Carbon $dt): bool
    {
        $m = (int)$dt->minute;
        return $m === 0 || $m === 30;
    }

    private function isSuperDoctor(User $user): bool
    {
        // uses users.is_super_doctor (boolean/tinyint)
        return (bool) ($user->is_super_doctor ?? false);
    }

     public function byPatient($patientId, Request $request)
    {
        $consultations = Consultation::with(['doctor','location','medicalHistory','creator','patient', 'prescription'])
    ->where('patient_id', $patientId)
    ->latest()
    ->get()
    ->map(function ($c) {
        return [
            'id'            => $c->id,
            'creator'       => $c->creator ? ['id'=>$c->creator->id,'name'=>$c->creator->name] : null,
            'prescription'       => $c->prescription ? ['id'=>$c->prescription->id,'id'=>$c->prescription->id] : null,
            'start_at'      => $c->start_at,
            'end_at'        => $c->end_at,
            'reason'        => $c->reason,
            'instruction'   => $c->instruction,
            'examination'   => $c->examination,
            'diagnosis'     => $c->diagnosis,
            'management'    => $c->management,
            'investigation' => $c->investigation,
            'request_forms' => $c->request_forms,
            'status'        => $c->status,
            'created_at'    => $c->created_at,

            'doctor'        => $c->doctor ? ['id'=>$c->doctor->id,'name'=>$c->doctor->name] : null,
            'location'      => $c->location ? ['id'=>$c->location->id,'name'=>$c->location->name] : null,
            // both shapes for compatibility:
            'medical_history'   => $c->medicalHistory ? ['history' => $c->medicalHistory->history] : null,
            'medical_histories' => $c->medicalHistory ? [['history' => $c->medicalHistory->history]] : [],
        ];
    });

return response()->json([
    'success' => true,
    'message' => 'Consultations fetched.',
    'data'    => $consultations,
]);

        $data = $consultations->map(function ($c) {
            // prefer hasOne medicalHistory; fall back to first of medicalHistories if present
            $mh = $c->medicalHistory
                ?: (($c->relationLoaded('medicalHistories') && $c->medicalHistories->count())
                    ? $c->medicalHistories->first()
                    : null);

            return [
                'id'            => $c->id,
                'start_at'      => optional($c->start_at)->toISOString() ?? (string) $c->start_at,
                'end_at'        => optional($c->end_at)->toISOString() ?? (string) $c->end_at,
                'reason'        => $c->reason,
                'instruction'   => $c->instruction,
                'examination'   => $c->examination,
                'diagnosis'     => $c->diagnosis,
                'management'     => $c->management,
                'investigation' => $c->investigation,
                'request_forms' => $c->request_forms,
                'status'        => $c->status,
                'created_at'    => optional($c->created_at)->toISOString() ?? (string) $c->created_at,

                'doctor' => $c->doctor ? [
                    'id' => $c->doctor->id,
                    'name' => $c->doctor->name,
                    'is_super_doctor' => (bool) ($c->doctor->is_super_doctor ?? false),
                ] : null,

                'location' => $c->location ? [
                    'id' => $c->location->id,
                    'name' => $c->location->name,
                ] : null,

                'creator' => $c->creator ? [
                    'id' => $c->creator->id,
                    'name' => $c->creator->name,
                ] : null,

                'medical_history' => $mh ? [
                    'history' => $mh->history,
                ] : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'message' => 'Consultations fetched.',
            'data'    => $data,
        ], 200);
    }
}
