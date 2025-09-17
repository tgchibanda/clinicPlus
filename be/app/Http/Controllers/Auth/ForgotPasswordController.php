<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;


class ForgotPasswordController extends Controller
{
    public function forgot() {
        Log::debug(__METHOD__ . ' bof');

        $credentials = request()->validate(['email' => 'required|email']);
        Password::sendResetLink($credentials);

        Log::debug(__METHOD__ . ' eof');
        return response()->json(["msg" => 'Reset password link sent on your email id.']);
    }


    public function reset() {
        Log::debug(__METHOD__ . ' bof');

        $credentials = request()->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string'
        ]);

        $resetPasswordStatus = Password::reset($credentials, function ($user, $password) {
            $user->password = bcrypt($password);
            $user->save();
        });

        if ($resetPasswordStatus == Password::INVALID_TOKEN) {
            Log::debug(__METHOD__ . ' Invalid token provided');

            return response()->json([
                "success" => true,
                "message" => "Invalid token provided",
                "data" => $resetPasswordStatus
            ], 400);
    
        }
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            "success" => true,
            "message" => "Password has been successfully changed.",
            "data" => $resetPasswordStatus
        ], 200);
    }
}
