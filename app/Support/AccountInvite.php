<?php

namespace App\Support;

use App\Mail\ForgotPasswordEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AccountInvite
{
    /** Gives the account a random password nobody knows and mails a link to set a real one. */
    public static function send(User $user): void
    {
        $user->forceFill(['password' => Hash::make(Str::password(40))])->save();

        $token = Password::broker()->createToken($user);
        Mail::to($user->email)->send(new ForgotPasswordEmail($token, $user->email, welcome: true));
    }
}
