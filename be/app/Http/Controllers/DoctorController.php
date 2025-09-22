<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Location;
use App\Models\Consultation;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    /**
     * GET /api/doctors?location_id=ID
     * List doctors, optionally filtered by location.
     * A "doctor" is a user whose role is "doctor" in user_roles table.
     */
    public function index(Request $request)
    {
        $locationId = $request->query('location_id');

        $doctors = User::query()
            // join user_roles (role stored here)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                  ->from('user_roles')
                  ->whereColumn('user_roles.user_id', 'users.id')
                  ->where('user_roles.role', 'doctor');
            })
            // optional filter by location
            ->when($locationId, function ($q) use ($locationId) {
                $q->where(function ($q2) use ($locationId) {
                    // doctors assigned to this location OR super doctors (can float)
                    $q2->where('users.location_id', $locationId)
                       ->orWhere('users.is_super_doctor', true);
                });
            })
            ->with(['location:id,name']) // eager load location name
            ->orderBy('name')
            ->get([
                'id', 'name', 'email', 'location_id', 'is_super_doctor',
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Doctors retrieved successfully.',
            'data'    => $doctors,
        ], 200);
    }

    /**
     * GET /api/doctors/{doctor}/availability?date=YYYY-MM-DD&location_id=ID
     *
     * Returns 30-min slots for the day.
     * Super doctor rule: if any booking exists that day, doctor is "locked" to that location for the whole day.
     * If no booking yet and a location_id is requested, we *advertise* availability for that location.
     * (The lock is effectively created by the first saved booking.)
     */
    public function availability(Request $request, User $doctor)
    {
        // Validate inputs
        $validated = $request->validate([
            'date'        => ['required', 'date_format:Y-m-d'],
            'location_id' => ['nullable', 'integer', Rule::exists('locations', 'id')],
        ]);

        // Ensure this user is a doctor (role via user_roles)
        $isDoctor = DB::table('user_roles')
            ->where('user_id', $doctor->id)
            ->where('role', 'doctor')
            ->exists();

        if (!$isDoctor) {
            return response()->json([
                'success' => false,
                'message' => 'User is not a doctor.',
            ], 422);
        }

        $date = Carbon::createFromFormat('Y-m-d', $validated['date'])->startOfDay();
        $dayStart = $date->copy()->setTime(8, 0);   // 08:00
        $dayEnd   = $date->copy()->setTime(17, 0);  // 17:00
        $slotMinutes = 30;

        // Existing consultations for the day
        $bookings = Consultation::query()
            ->where('doctor_id', $doctor->id)
            ->whereDate('start_at', $date->toDateString())
            ->orderBy('start_at')
            ->get(['id', 'location_id', 'start_at', 'end_at']);

        // Determine locked location for super doctor (or normal doctor’s fixed location)
        $lockedLocationId = null;

        if ($doctor->is_super_doctor) {
            // If super doctor has any booking that day, lock to that location
            $firstBooking = $bookings->first();
            if ($firstBooking) {
                $lockedLocationId = $firstBooking->location_id;
            } else {
                // No bookings yet. If a location_id was requested, *advertise* availability there.
                $lockedLocationId = $validated['location_id'] ?? null;
            }
        } else {
            // Normal doctors are tied to their own location
            $lockedLocationId = $doctor->location_id;
        }

        // If we still don't have a location (super doc, no bookings, no requested location),
        // we can show no availability until the UI picks a location.
        if (!$lockedLocationId) {
            return response()->json([
                'success' => true,
                'message' => 'No location selected. Please choose a location for availability.',
                'data'    => [
                    'doctor_id'         => $doctor->id,
                    'doctor_is_super'   => (bool) $doctor->is_super_doctor,
                    'locked_location_id'=> null,
                    'work_hours'        => [
                        'start' => $dayStart->toTimeString(),
                        'end'   => $dayEnd->toTimeString(),
                    ],
                    'booked_slots'      => [],   // unknown until location chosen
                    'available_slots'   => [],   // unknown until location chosen
                ],
            ], 200);
        }

        // Generate all 30-min slots
        $allSlots = $this->generateSlots($dayStart, $dayEnd, $slotMinutes);

        // Build a set of booked slots for that location on that day
        $bookedSlots = [];
        foreach ($bookings as $b) {
            // Only consider bookings at the locked location
            if ((int) $b->location_id !== (int) $lockedLocationId) {
                continue;
            }
            $start = Carbon::parse($b->start_at);
            $end   = Carbon::parse($b->end_at ?? $start->copy()->addMinutes($slotMinutes));

            $bookedSlots = array_merge($bookedSlots, $this->expandToSlots($start, $end, $slotMinutes));
        }
        $bookedSlots = array_values(array_unique($bookedSlots));

        // Available = allSlots minus bookedSlots
        $available = array_values(array_diff($allSlots, $bookedSlots));

        // Response with helpful context
        return response()->json([
            'success' => true,
            'message' => 'Availability retrieved successfully.',
            'data'    => [
                'doctor_id'           => $doctor->id,
                'doctor_name'         => $doctor->name,
                'doctor_is_super'     => (bool) $doctor->is_super_doctor,
                'locked_location_id'  => (int) $lockedLocationId,
                'locked_location'     => optional(Location::find($lockedLocationId))->only(['id', 'name']),
                'date'                => $date->toDateString(),
                'slot_minutes'        => $slotMinutes,
                'work_hours'          => [
                    'start' => $dayStart->toTimeString(),
                    'end'   => $dayEnd->toTimeString(),
                ],
                'booked_slots'        => $bookedSlots,   // ['08:00', '08:30', ...]
                'available_slots'     => $available,     // ['09:00', '09:30', ...]
            ],
        ], 200);
    }

    /**
     * Generate 30-min slot labels between start and end (inclusive start, exclusive end).
     * e.g., ['08:00', '08:30', '09:00', ...]
     */
    protected function generateSlots(Carbon $start, Carbon $end, int $minutes): array
    {
        $slots = [];
        $cursor = $start->copy();
        while ($cursor->lt($end)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($minutes);
        }
        return $slots;
    }

    /**
     * Expand a booking interval into 30-min slot labels.
     */
    protected function expandToSlots(Carbon $start, Carbon $end, int $minutes): array
    {
        // Normalize to slot grid
        $s = $start->copy()->minute(intval($start->minute / $minutes) * $minutes)->second(0);
        $e = $end->copy()->second(0);

        $slots = [];
        $cursor = $s->copy();
        while ($cursor->lt($e)) {
            $slots[] = $cursor->format('H:i');
            $cursor->addMinutes($minutes);
        }
        return $slots;
    }
}
