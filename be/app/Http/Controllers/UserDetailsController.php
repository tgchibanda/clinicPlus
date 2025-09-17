<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\User;
use App\Models\Upload;
use App\Models\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domains\Emails;

class UserDetailsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        Log::debug(__METHOD__ . ' bof');

        $contact = User::select(
            'user_roles.*',
            'users.*',
            'contacts.*',
            'users.id as a',
        )
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->leftjoin('contacts', 'contacts.user_id', '=', 'users.id')
            ->orderby('user_roles.role')
            ->get();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Users retrieved successfully.",
            "data" => $contact
        ], 200);
    }

    /**
     * Accept user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function acceptUser(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        if (UserRole::where('user_id', $request->id)->exists()) {
            $consultation = UserRole::where('user_id', $request->id)->first();
            $consultation->status = 'active';
            $consultation->save();

            Log::debug(__METHOD__ . ' eof');

            $sendEmail = new Emails();
            $sendEmail->sendEmail(
                $sendEmail->getEmail($request->id, 'user'),
                "Congratulations, Your doctor account has been approved, kindly login to start use",
                'clinicPlus Doctor Account Reviewed'
            );
            $sendEmail->sendEmail(
                env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
                "clinicPlus Doctor Account Reviewed ID = {$request->id}",
                'clinicPlus Doctor Account Reviewed'
            );
            

            return response()->json([
                "success" => true,
                "message" => "acceptUser saved successfully.",
                "data" => $consultation
            ], 200);
        } else {
            Log::debug(__METHOD__ . ' eof');
            return response()->json([
                "success" => true,
                "message" => "acceptUser not found.",
            ], 404);
        }
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

        $mainDetails = User::select(
            'user_roles.*',
            'users.*',
            'contacts.*',
            'doctor_details.*',
            'users.id as a',
        )
            ->join('user_roles', 'user_roles.user_id', '=', 'users.id')
            ->leftjoin('doctor_details', 'doctor_details.user_id', '=', 'users.id')
            ->leftjoin('contacts', 'contacts.user_id', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->orderby('user_roles.role')
            ->get();

        $profile = User::select('upload')
            ->join('uploads', 'uploads.user_id', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->where('uploads.description', '=', 'profile')
            ->get();

        $files = User::select('uploads.*')
            ->join('uploads', 'uploads.user_id', '=', 'users.id')
            ->where('users.id', '=', $id)
            ->where('uploads.description', '<>', 'profile')
            ->get();

        Log::debug(__METHOD__ . ' eof');

        $data = ['main_details' => $mainDetails, 'profile' => $profile, 'files' => $files];
        return response()->json([
            "success" => true,
            "message" => "Users retrieved successfully.",
            "data" => $data
        ], 200);
    }

}
