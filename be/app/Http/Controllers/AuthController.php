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
            "New Account Created ID = {$user->id}",
            'clinicPlus Account Created'
        );

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
        Log::debug(__METHOD__ . ' BOF');

        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
            'remember_me' => 'boolean'
        ]);

        $credentials = $request->only('email', 'password');

        /**
         * 1️⃣ Attempt authentication first
         */
        if (!Auth::attempt($credentials)) {
            return response()->json([
                'message' => 'Unauthorized - Invalid email or password'
            ], 401);
        }

        /**
         * 2️⃣ Get authenticated user
         */
        $user = Auth::user();

        /**
         * 3️⃣ Get user role & status
         */
        $userRole = \DB::table('users')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->select(
                'users.id',
                'users.email',
                'users.name',
                'user_roles.role',
                'user_roles.status'
            )
            ->where('users.id', $user->id)
            ->first();

        /**
         * 4️⃣ Block suspended users
         */
        if ($userRole && $userRole->status === 'suspended') {
            Auth::logout();

            return response()->json([
                'message' => 'Account Suspended - Please contact the administrator'
            ], 403);
        }

        /**
         * 5️⃣ Create token
         */
        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me) {
            $token->expires_at = Carbon::now()->addWeeks(1);
        }

        $token->save();

        /**
         * 6️⃣ Generate & send OTP
         */
        $general = new General();
        $otp = $general->generateRandomString(6);

        $emailService = new Emails();
        $emailService->sendEmail(
            $user->email,
            "Your OTP is {$otp}",
            "clinicPlus OTP {$otp}"
        );

        $emailService->sendEmail(
            env('ADMIN_EMAIL', 'admin@clinicPlus.com'),
            "{$user->email} generated OTP is {$otp}",
            'clinicPlus OTP generated'
        );

        Log::debug(__METHOD__ . ' EOF');

        /**
         * 7️⃣ Final response
         */
        return response()->json([
            'accessToken' => $tokenResult->accessToken,
            'token_type'  => 'Bearer',
            'expires_at'  => Carbon::parse($token->expires_at)->toDateTimeString(),
            'user_id'     => $user->id,
            'email'       => $user->email,
            'fullname'    => $user->name,
            'role'        => $userRole->role ?? null,
            'status'      => $userRole->status ?? null,
            'avatar'      => $user->avatar,
            'otp'         => $otp,
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
