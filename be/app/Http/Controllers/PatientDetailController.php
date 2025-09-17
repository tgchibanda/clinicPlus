<?php

namespace App\Http\Controllers;

use App\Models\PatientDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domains\Emails;

class PatientDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof' . print_r($request['user']));

        $this->validate($request, [
            'user_id' => 'required|numeric|max:191',
            'fullname' => 'required',
            'email' => 'required|email',
            'address_line1' => 'required',
            'address_line2' => 'required',
            'city' => 'required|alpha_num',
            'mobile_no' => 'required|numeric',
            'gps' => 'required',
            'contact_person' => 'required',
            'mobile_no_contact_person' => 'required',
            'dob' => 'required',
            'gender' => 'required'
        ]);

        $patientDetail = PatientDetail::create([
            'user_id' => $request['user_id'],
            'email' => $request['email'],
            'fullname' => $request['fullname'],
            'address_line1' => $request['address_line1'],
            'address_line2' => $request['address_line2'],
            'address_line3' => $request['address_line3'],
            'city' => $request['city'],
            'mobile_no' => $request['mobile_no'],
            'gps' => $request['gps'],
            'contact_person' => $request['contact_person'],
            'mobile_no_contact_person' => $request['mobile_no_contact_person'],
            'dob' => $request['dob'],
            'gender' => $request['gender']
        ]);

        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $sendEmail->getEmail($request['user_id'], 'user'),
            "Congratulations, You have created {$request['fullname']} as a patient on clinicPlus",
            'clinicPlus Patient Created'
        );
        $sendEmail->sendEmail(
            $request['email'],
            "Congratulations {$request['fullname']}, You have been created as a patient on clinicPlus",
            'clinicPlus Patient Created'
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
            "New Patient Created id {$patientDetail->id}", 'clinicPlus Patient Created');

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "PatientDetail saved successfully.",
            "data" => $patientDetail
        ], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        Log::debug(__METHOD__ . ' bof');

        $patientDetail = PatientDetail::select(
            'patient_details.email',
            'patient_details.gps',
            'patient_details.dob',
            'patient_details.gender',
            'patient_details.mobile_no',
            'patient_details.fullname',
            'patient_details.city',
            'patient_details.city',
            'patient_details.id'
        )
            ->join('users', 'users.id', '=', 'patient_details.user_id')
            ->where('patient_details.user_id', '=', $id)
            ->orderBy("patient_details.created_at", "desc")
            ->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "PatientDetail retrieved successfully.",
            "data" => $patientDetail
        ], 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request, [
            'user_id' => 'required|numeric|max:191',
            'fullname' => 'required',
            'email' => 'required|email',
            'address_line1' => 'required',
            'address_line2' => 'required',
            'city' => 'required|alpha_num',
            'mobile_no' => 'required|numeric',
            'gps' => 'required|numeric',
            'contact_person' => 'required',
            'mobile_no_contact_person' => 'required',
            'dob' => 'required',
            'gender' => 'required'
        ]);

        if (PatientDetail::where('id', $id)->exists()) {
            $patientDetail = PatientDetail::find($id);
            $patientDetail->user_id = is_null($request->user_id) ? $patientDetail->user_id : $request->user_id;
            $patientDetail->fullname = is_null($request->fullname) ? $patientDetail->fullname : $request->fullname;
            $patientDetail->address_line1 = is_null($request->address_line1) ? $patientDetail->address_line1 : $request->address_line1;
            $patientDetail->address_line2 = is_null($request->address_line2) ? $patientDetail->address_line2 : $request->address_line2;
            $patientDetail->address_line3 = is_null($request->address_line3) ? $patientDetail->address_line3 : $request->address_line3;
            $patientDetail->city = is_null($request->city) ? $patientDetail->city : $request->city;
            $patientDetail->mobile_no = is_null($request->mobile_no) ? $patientDetail->mobile_no : $request->mobile_no;
            $patientDetail->gps = is_null($request->gps) ? $patientDetail->gps : $request->gps;
            $patientDetail->contact_person = is_null($request->contact_person) ? $patientDetail->contact_person : $request->contact_person;
            $patientDetail->mobile_no_contact_person = is_null($request->mobile_no_contact_person) ? $patientDetail->mobile_no_contact_person : $request->mobile_no_contact_person;
            $patientDetail->gender = is_null($request->gender) ? $patientDetail->gender : $request->gender;
            $patientDetail->dob = is_null($request->dob) ? $patientDetail->dob : $request->dob;

            $patientDetail->save();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "PatientDetail updated successfully.",
                "data" => $patientDetail
            ], 201);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "PatientDetail not found.",
            ], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Log::debug(__METHOD__ . ' bof');

        if (PatientDetail::where('id', $id)->exists()) {
            $patientDetail = PatientDetail::find($id);
            $patientDetail->delete();

            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "message" => "records deleted"
            ], 202);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "message" => "Student not found"
            ], 404);
        }
    }
}
