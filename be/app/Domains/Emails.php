<?php

namespace App\Domains;

use App\Jobs\SendEmail;
use App\Models\User;

class Emails
{
    public function sendEmail($email, $emailMsg, $subject, $name = 'clinicPlus User')
    {
        $details = [
            'email' => $email,
            'subject' => $subject,
            'data' => [
                'name' => $name,
                'email' => $email,
                'data' => $emailMsg
            ]
        ];
        SendEmail::dispatch($details);
    }

    public function getEmail($id, $type)
    {
        if ($type == 'user') {
            $user = User::select('email')
            ->where('users.id', '=', $id)
            ->first();
            return $user->email;
        } else {
            return '';
        }
    }
}
