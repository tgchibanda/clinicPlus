<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Consultation;
use App\Models\Payment;
use App\Models\UserRole;
use App\Domains\Fees;
use App\Models\User;
use App\Models\PatientDetail;

class DashboardController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  string  $account_type
     * @return \Illuminate\Http\Response
     */
    public function getData($id, $account_type)
    {
        Log::debug(__METHOD__ . ' bof');
        $month = date('m');
        $year = date('Y');
        $fees = new Fees();
        $openConsultations = Consultation::where("status", "2")->count();
        if ($account_type == 'admin') {
            $pendingUsers = UserRole::where("status", "pending")->count();
            $users = UserRole::where("role", "user")->count();
            $doctors = UserRole::where("role", "doctor")->count();
            $patients = PatientDetail::count();
            $payments = Payment::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereIn('payments.status', ['paid', 'payout'])
                ->sum('amount');

            $calcFees = $fees->GenerateFees($payments);

            $data = [
                'pendingUsers' => $pendingUsers,
                'openConsultations' => $openConsultations,
                'amount_gross' => $calcFees['amount_gross'],
                'fees' => $calcFees['fees'],
                'amount_net' => $calcFees['amount_net'],
                'users' => $users,
                'doctors' => $doctors,
                'patients' => $patients,
            ];

            return response()->json([
                "success" => true,
                "message" => "admin data retrieved successfully.",
                "data" => $data
            ], 200);
        }
        if ($account_type == 'doctor') {
            $payments = Payment::join('consultations', 'consultations.id', '=', 'payments.consultation_id')
                ->whereYear('payments.created_at', $year)
                ->whereMonth('payments.created_at', $month)
                ->where('consultations.doctor_id', $id)
                ->whereIn('payments.status', ['paid', 'payout'])
                ->sum('amount');
            $pastAttendances = Consultation::whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('doctor_id', $id)
                ->count();

            $calcFees = $fees->GenerateFees($payments);

            $data = [
                'pastAttendances' => $pastAttendances,
                'openConsultations' => $openConsultations,
                'amount_gross' => $calcFees['amount_gross'],
                'fees' => $calcFees['fees'],
                'amount_net' => $calcFees['amount_net'],
            ];

            return response()->json([
                "success" => true,
                "message" => "doctor data retrieved successfully.",
                "data" => $data
            ], 200);
        }
        if ($account_type == 'user') {
            $payments = Payment::where('user_id', $id)->whereIn('payments.status', ['paid', 'payout'])->sum('amount');
            $paymentsThisMonth = Payment::where('user_id', $id)
                ->whereYear('payments.created_at', $year)
                ->whereMonth('payments.created_at', $month)
                ->whereIn('payments.status', ['paid', 'payout'])
                ->sum('amount');
            $pendingConsultations = Consultation::join('patient_details', 'patient_details.id', '=', 'consultations.patient_id')
                ->where('patient_details.user_id', $id)
                ->where("status", "2")
                ->count();
            $pastAttendances = Consultation::join('patient_details', 'patient_details.id', '=', 'consultations.patient_id')
                ->where('patient_details.user_id', $id)
                ->where("status", "4")
                ->count();


            $calcFees = $fees->GenerateFees($payments);

            $data = [
                'pendingConsultations' => $pendingConsultations,
                'pastAttendances' => $pastAttendances,
                'payments' => $payments,
                'paymentsThisMonth' => $paymentsThisMonth,
            ];

            return response()->json([
                "success" => true,
                "message" => "doctor data retrieved successfully.",
                "data" => $data
            ], 200);
        }
    }
}
