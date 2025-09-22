<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use App\Domains\Emails;
use App\Models\PatientDetail;
use Illuminate\Support\Facades\Log;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::with('doctor')->latest()->paginate(15);
        return response()->json([
            "success" => true,
            "message" => "Patient Details retrieved successfully.",
            "data" => $patients
        ], 200);
    }

    public function create()
    {
        $doctors = User::where('role', 'doctor')->get();
        return view('patients.create', compact('doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|numeric|max:191',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'gender' => 'nullable|in:Male,Female',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string|max:255'
        ]);

        $patient =  Patient::create([
            'user_id' => $request['user_id'],
            'first_name' => $request['first_name'],
            'last_name' => $request['last_name'],
            'phone' => $request['phone_number'],
            'email' => $request['email'],
            'date_of_birth' => $request['dob'],
            'gender' => $request['gender'],
            'address' => $request['address'],
            'emergency_contact' => $request['emergency_contact']
        ]);

        

        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $sendEmail->getEmail($request['user_id'], 'user'),
            "Congratulations, You have created {$request['first_name']} as a patient on clinicPlus",
            'clinicPlus Patient Created'
        );
        $sendEmail->sendEmail(
            $request['email'],
            "Congratulations {$request['first_name']}, You have been created as a patient on clinicPlus",
            'clinicPlus Patient Created'
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
            "New Patient Created id {$patient->id}", 'clinicPlus Patient Created');

        return response()->json([
            "success" => true,
            "message" => "Patient Details saved successfully.",
            "data" => $patient
        ], 201);
    }

    public function show(Patient $patient)
    {
        $patient->load('doctor', 'prescriptions.items.drug');
        return view('patients.show', compact('patient'));
    }

    public function walkInPatientDetails($id)
    {
        $patient = Patient::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Patient Details retrieved successfully.',
            'data'    => $patient,
        ]);
    }

    public function assignDoctor(Request $request, Patient $patient)
    {
        $request->validate([
            'doctor_id' => 'required|exists:users,id'
        ]);

        $patient->update(['assigned_doctor_id' => $request->doctor_id]);

        return back()->with('success', 'Doctor assigned successfully!');
    }

}