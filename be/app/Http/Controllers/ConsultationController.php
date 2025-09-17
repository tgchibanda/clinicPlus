<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use App\Models\MedicalHistory;
use App\Models\PatientDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use PDF;
use App\Domains\Emails;
use App\Models\User;

class ConsultationController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request, [
            'patient_id' => 'required|numeric|max:191',
            'reason' => 'required',
        ]);

        $consultation = Consultation::create([
            'patient_id' => $request['patient_id'],
            'reason' => $request['reason'],
            'status' => 0
        ]);
        $medicalHistory = MedicalHistory::create([
            'patient_id' => $request['patient_id'],
            'history' => $request['past_medical_history']
        ]);

        // $medicals = array_merge($consultation, $medicalHistory);
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "consultation saved successfully.",
            "data" => $consultation
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function consultationDetails($account, $id)
    {
        Log::debug(__METHOD__ . ' bof');

        if ($account == 'user') {
            Log::debug(__METHOD__ . ' userQuery');
            $patientDetail = PatientDetail::select(
                'consultations.*',
                'consultations.created_at as c_date',
                'patient_details.*',
                'statuses.status_text',
                'statuses.status_description',
                'consultations.id as a',
                'users.name'
            )
                ->join('users', 'users.id', '=', 'patient_details.user_id')
                ->join('consultations', 'consultations.patient_id', '=', 'patient_details.id')
                ->join('statuses', 'statuses.status_level', '=', 'consultations.status')
                ->where('patient_details.user_id', '=', $id)
                ->orderBy("consultations.created_at", "desc")
                ->get();
        } else if ($account == 'admin') {
            Log::debug(__METHOD__ . ' adminquery');
            $patientDetail = PatientDetail::select(
                'consultations.*',
                'consultations.created_at as c_date',
                'patient_details.*',
                'statuses.status_text',
                'statuses.status_description',
                'consultations.id as a',
                'users.name'
            )
                ->join('users', 'users.id', '=', 'patient_details.user_id')
                ->join('consultations', 'consultations.patient_id', '=', 'patient_details.id')
                ->join('statuses', 'statuses.status_level', '=', 'consultations.status')
                ->orderBy("consultations.created_at", "desc")
                ->get();
        } else if ($account == 'doctor') {
            Log::debug(__METHOD__ . ' doctorQuery');
            $patientDetail = PatientDetail::select(
                'consultations.*',
                'consultations.created_at as c_date',
                'patient_details.*',
                'statuses.status_text',
                'statuses.status_description',
                'consultations.id as a',
                'users.name'
            )
                ->join('users', 'users.id', '=', 'patient_details.user_id')
                ->join('consultations', 'consultations.patient_id', '=', 'patient_details.id')
                ->join('statuses', 'statuses.status_level', '=', 'consultations.status')
                ->whereIn('consultations.status', [2, 3, 4])
                ->where(function ($patientDetail) use ($id) {
                    $patientDetail->where('consultations.doctor_id', '=', $id)
                        ->orWhereNull('consultations.doctor_id');
                })
                ->orderBy("consultations.created_at", "desc")
                ->get();
        }

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "PatientDetail retrieved successfully.",
            "data" => $patientDetail
        ], 200);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getConsultation($id)
    {
        Log::debug(__METHOD__ . ' bof');
        $consultationDetails = PatientDetail::select(
            'patient_details.*',
            'consultations.*',
            'statuses.*',
            'payments.*',
            'medical_histories.history',
            'consultations.created_at as consultation_date',
        )
            ->join('users', 'users.id', '=', 'patient_details.user_id')
            ->join('consultations', 'consultations.patient_id', '=', 'patient_details.id')
            ->join('statuses', 'statuses.status_level', '=', 'consultations.status')
            ->join('payments', 'payments.consultation_id', '=', 'consultations.id')
            ->leftjoin('medical_histories', 'medical_histories.patient_id', '=', 'patient_details.id')
            ->where('consultations.id', '=', $id)
            ->get();

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "getConsultation retrieved successfully.",
            "data" => $consultationDetails
        ], 200);
    }

    /**
     * Accept consultation
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptConsultation(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        if (Consultation::where('id', $request->id)->exists()) {
            $consultation = Consultation::find($request->id);
            $consultation->doctor_id = is_null($request->doctor_id) ? $consultation->doctor_id : $request->doctor_id;
            $consultation->status = 3;
            $consultation->save();

            $paymentData = Consultation::select('amount', 'fullname', 'payments.user_id', 'email')
            ->join('patient_details', 'consultations.patient_id', '=', 'patient_details.id')
            ->join('payments', 'payments.consultation_id', '=', 'consultations.id')
            ->where('consultations.id', $request->id)
            ->get();

            $user = User::select('name', 'email')->where('users.id', '=', $request->doctor_id)->first();
            $sendEmail = new Emails();

            Log::debug(__METHOD__ . ' Sending user email ' . $sendEmail->getEmail($paymentData[0]->user_id, 'user'));
            $sendEmail->sendEmail(
                $sendEmail->getEmail($paymentData[0]->user_id, 'user'),
                "Your Consultation has been booked by DR {$user->name}, The patient will be visited with
                 in 24hrs.",
                'clinicPlus Consultation Confirmed'
            );

            Log::debug(__METHOD__ . ' Sending patient email ' . $paymentData[0]->email);
            $sendEmail->sendEmail(
                $paymentData[0]->email,
                "Your Consultation has been booked by DR {$user->name}, The doctor will be visited with
                 in 24hrs.",
                'clinicPlus Consultation Confirmed'
            );

            Log::debug(__METHOD__ . ' Sending doctor email ' . $user->email);
            $sendEmail->sendEmail(
                $user->email,
                "You have booked a consultation to see {$paymentData[0]->fullname}, you have to visit with
                 in 24hrs.",
                'clinicPlus Consultation Confirmed'
            );
            $sendEmail->sendEmail(
                env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
                "New Consultation has been booked by DR {$user->name}",
                'clinicPlus Consultation Confirmed'
            );
    

            Log::debug(__METHOD__ . ' eof');

            return response()->json([
                "success" => true,
                "message" => "acceptConsultation saved successfully.",
                "data" => $consultation
            ], 200);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "acceptConsultation not found.",
            ], 404);
        }
    }


    /**
     * doctorNotes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function doctorNotes(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request, [
            'consultation_id' => 'required|numeric|max:191',
            'examination' => 'required',
        ]);

        if (Consultation::where('id', $request->consultation_id)->exists()) {
            $consultation = Consultation::find($request->consultation_id);
            $consultation->management = is_null($request->management) ? $consultation->management : $request->management;
            $consultation->examination = is_null($request->examination) ? $consultation->examination : $request->examination;
            $consultation->diagnosis = is_null($request->diagnosis) ? $consultation->diagnosis : $request->diagnosis;
            $consultation->investigation = is_null($request->investigation) ? $consultation->investigation : $request->investigation;
            $consultation->request_forms = is_null($request->request_forms) ? $consultation->pathology : $request->request_forms;
            $consultation->status = 4;
            $consultation->save();

            Log::debug(__METHOD__ . ' eof');

            return response()->json([
                "success" => true,
                "message" => "doctorNotes saved successfully.",
                "data" => $consultation
            ], 200);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "doctorNotes not found.",
            ], 404);
        }
    }

    public function generateRequestForm($consultation_id, $form_type)
    {
        Log::debug(__METHOD__ . ' bof');

        $details = PatientDetail::select(
            'patient_details.fullname',
            'patient_details.dob',
            'patient_details.address_line1',
            'patient_details.address_line2',
            'patient_details.address_line3',
            'patient_details.city',
            'users.name',
        )
            ->join('consultations', 'consultations.patient_id', '=', 'patient_details.id')
            ->join('users', 'users.id', '=', 'consultations.doctor_id')
            ->where('consultations.id', '=', $consultation_id)
            ->get();

        Log::debug(__METHOD__ . ' eof');

        return $this->generateRequestPDF($form_type, $details[0]);
    }

    public function generateRequestPDF($template, $details) {
        Log::debug(__METHOD__ . ' bof');
        $pdf = PDF::loadView('email.' . $template, ['details' => $details]);

        return $pdf->download($template . '.pdf');
    }
}
