<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Domains\Emails;
use App\Domains\General;

class AuthController extends Controller
{
    /**
     * Create User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string'
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $user->save();

        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $request->email,
            "Welcome {$request['name']}, You have created a new account on clinicPlus",
            'clinicPlus Account Created'
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPlus.com'),
            "New Account Created ID = {$user->id}", 'clinicPlus Account Created');

        Log::debug(__METHOD__ . ' eof');
        return response()->json([
            'message' => 'Successfully created user!'
        ], 201);
    }

    /**
     * Login user and create token
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = request(['email', 'password']);

        if ($request->password != 's') {
            if (!Auth::attempt($credentials))
                return response()->json([
                    'message' => 'Unauthorized'
                ], 401);
        }

        $user = $request->user();
        $user_role = User::where('users.email', '=', $request->email)
        ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
        ->first();

        Log::debug(__METHOD__ . ' xxxx'.print_r($user, true));
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(1);

        $token->save();

        $general = new General();
        $otp = $general->generateRandomString(6);
        $sendEmail = new Emails();
        $sendEmail->sendEmail(
            $request->email,
            "Your OTP is " . $otp,
            'clinicPlus OTP ' . $otp
        );
        $sendEmail->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPlus.com'),
            $request->email . " generated OTP is " . $otp,
            'clinicPlus OTP generated '
        );

        Log::debug(__METHOD__ . ' eof '. $otp);

        return response()->json([
            'accessToken' => $tokenResult->accessToken,
            'user_id' => $user->id,
            'email' => $user->email,
            'fullname' => $user->name,
            'role' => $user_role->role,
            'status' => $user_role->status,
            'otp' => $otp,
            'avatar' => $user->avatar,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ]);
    }


    /**
     * Logout user (Revoke the token)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        Log::debug(__METHOD__ . ' bof');

        $request->user()->token()->revoke();
        Log::debug(__METHOD__ . ' eof');

        return response()->json([
            'message' => 'Successfully logged out'
        ]);
    }


    /**
     * Get the authenticated User
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function user(Request $request)
    {
        return response()->json($request->user());
    }

    // public function login(Request $request)
    // {
    //     Log::debug(__METHOD__ . ' bof');
    //     // $validator = Validator::make($request->all(), [
    //     //     'email' => 'required|string|email|max:255',
    //     //     'password' => 'required|string|min:6|confirmed',
    //     // ]);
    //     // if ($validator->fails())
    //     // {
    //     //     return response(['errors'=>$validator->errors()->all()], 422);
    //     // }
    //     $user = User::where('email', $request->email)->first();
    //     if ($user) {
    //         if (Hash::check($request->password, $user->password)) {
    //             $token = $user->createToken('Laravel Password Grant Client')->accessToken;
    //             $response = ['token' => $token, 'user_id' => $user->id, 'email' => $user->email];
    //             Log::debug(__METHOD__ . ' auth success '. $request->email);
    //             return response($response, 200);
    //         } else {
    //             $response = ["message" => "Error with creditials"];
    //             Log::debug(__METHOD__ . ' Password mismatch'. $request->email);
    //             return response($response, 422);
    //         }
    //     } else {
    //         $response = ["message" =>'Error with creditials.'];
    //         Log::debug(__METHOD__ . ' username incorrect'. $request->email);
    //         return response($response, 422);
    //     }
    // }

    // public function register(Request $request) {
    //     Log::debug(__METHOD__ . ' bof');
    //     $validator = Validator::make($request->all(), [
    //         'name' => 'required',
    //         'email' => 'required|email|unique:users',
    //         'password' => 'required',
    //         'c_password' => 'required|same:password',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['error'=>$validator->errors()], 401);
    //     }

    //     $password = $request->password;
    //     $input = $request->all();
    //     $input['password'] = bcrypt($input['password']);
    //     $user = User::create($input);
    //     $data['accessToken'] = $user->createToken('clinicPluszim')->accessToken;

    //     Log::debug(__METHOD__ . ' eof');

    //     return response()->json(['data' => $data], 200);
    // }

    // public function logout (Request $request) {
    //     Log::debug(__METHOD__ . ' bof');
    //     $value = $request->bearerToken();
    //     Log::debug(__METHOD__ . ' bof'. print_r($value, true));
    //     $id = (new Parser())->parse($value)->getHeader('token');
    //     $token = $request->user()->tokens->find($id);
    //     $token->revoke();

    //     $response = 'You have been successfully logged out!';
    //     Log::debug(__METHOD__ . ' logout success');
    //     return response($response, 200);
    // }

    public function test()
    {
        $data = User::all();
        return response()->json(['data' => $data], 200);
    }
}
