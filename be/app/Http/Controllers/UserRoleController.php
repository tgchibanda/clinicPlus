<?php

namespace App\Http\Controllers;


use App\Models\Contact;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domains\Emails;

class UserRoleController extends Controller
{
    //
        /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $this->validate($request,[
            'user_id' => 'required|numeric|max:191',
            'role' => 'required',
            'status' => 'required|max:191',
        ]);

        $userRole = UserRole::updateOrCreate([
            'user_id' => $request['user_id'],
            'role' => $request['user_account'],
            'status' => $request['status'],
        ]);

        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $sendEmail->getEmail($request['user_id'], 'user'),
            "Congratulations, You have updated your account type to {$request['user_account']}",
            'clinicPlus Account Role Updated'
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
            "clinicPlus Account Role Updated ID = {$request['user_id']}",
            'clinicPlus Account Role Updated'
        );
        if ($request['user_account'] == 'doctor') {
            $sendEmail->sendEmail(
                env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
                "Pending Doctor Account ID = {$request['user_id']}",
                'Pending Doctor Account'
            );
            $sendEmail->sendEmail(
                $sendEmail->getEmail($request['user_id'], 'user'),
                "Your account is under review, you will receieve an email once this process is done",
                'clinicPlus Doctor Account Review'
            );

        }

        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Data saved successfully.",
            "data" => $userRole
            ], 201);

    }
}
