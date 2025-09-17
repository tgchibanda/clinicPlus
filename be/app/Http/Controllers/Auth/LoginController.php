<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Exception;
use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use App\Domains\Emails;

class LoginController extends Controller
{

    protected $providers = [
        'github', 'facebook', 'google', 'twitter'
    ];

    public function redirectToProvider($driver)
    {
        Log::debug(__METHOD__ . ' bof');
        if (!$this->isProviderAllowed($driver)) {
            return response()->json(['errors' => "{$driver} is not currently supported"], Response::HTTP_BAD_REQUEST);
        }

        try {
            Log::debug(__METHOD__ . ' eof');
            return Socialite::driver($driver)->stateless()->redirect();
        } catch (Exception $e) {
            Log::error(__METHOD__ . ' SocialiteException' . print_r($e, true));
            return response()->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }
    }


    public function handleProviderCallback($driver)
    {
        Log::debug(__METHOD__ . ' bof');
        try {
            $user = Socialite::driver($driver)->stateless()->user();
        } catch (Exception $e) {
            Log::error(__METHOD__ . ' SocialiteException' . print_r($e, true));
            return response()->json(['errors' => [$e->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        Log::debug(__METHOD__ . ' check for email in returned user');
        Log::debug(__METHOD__ . ' eof');

        return empty($user->email)
            ? $this->sendFailedResponse("No email id returned from {$driver} provider.")
            : $this->loginOrCreateAccount($user, $driver);
    }

    protected function sendFailedResponse($message)
    {
        Log::debug(__METHOD__ . ' bof');
        header("Location: http://clinicPluszimbabwe.com/#/login?e=error&p={$message}");
    }

    protected function loginOrCreateAccount($providerUser, $driver)
    {
        Log::debug(__METHOD__ . ' bof');
        $user = User::where('email', $providerUser->getEmail())->first();

        // if user already found
        if ($user) {
            Log::debug(__METHOD__ . " {$providerUser->getEmail()} user already found");
            Log::debug(__METHOD__ . ' update the avatar and provider that might have changed');

            $user->update([
                'avatar' => $providerUser->avatar,
                'provider' => $driver,
                'provider_id' => $providerUser->id,
                'access_token' => $providerUser->token,
                'password' => bcrypt($providerUser->getId())
            ]);
        } else {
            Log::debug(__METHOD__ . "{$providerUser->getEmail()} not found create a new user");
            $user = User::create([
                'name' => is_null($providerUser->getName()) ? "Name not set" : $providerUser->getName(),
                'email' => $providerUser->getEmail(),
                'avatar' => $providerUser->getAvatar(),
                'provider' => $driver,
                'provider_id' => $providerUser->getId(),
                'access_token' => $providerUser->token,
                // user can use reset password to create a password
                'password' => bcrypt($providerUser->getId())
            ]);

            $sendEmail = new Emails();
            $sendEmail->sendEmail(
                $user->email,
                "Welcome {$user->name}, You have created a new account on clinicPlus",
                'clinicPlus Account Created'
            );
            $sendEmail->sendEmail(
                env('ADMIN_EMAIL', 'admin@clinicPluszimbabwe.com'),
                "New Account Created ID = {$user->id}", 'clinicPlus Account Created');

        }
        Log::debug(__METHOD__ . ' eof');
        header("Location: http://clinicPluszimbabwe.com/#/login/callback?e={$providerUser->getEmail()}&p={$providerUser->getId()}");
    }

    private function isProviderAllowed($driver)
    {
        Log::debug(__METHOD__ . ' bof');
        return in_array($driver, $this->providers) && config()->has("services.{$driver}");
    }
}
