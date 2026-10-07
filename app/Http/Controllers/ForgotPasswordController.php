<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ForgotPasswordController extends Controller
{
    private const SENT_MESSAGE = 'Jika email terdaftar, link reset sudah dikirim.';

    public function index()
    {
        return view('forgot-password.index');
    }

    public function expired()
    {
        return view('forgot-password.expired');
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        // Same response for every outcome, and the mail goes out after the response,
        // so neither the text nor the response time reveals which emails exist.
        $credentials = $request->only('email');
        dispatch(fn () => Password::sendResetLink($credentials))->afterResponse();

        return to_route('login')->with('msg', self::SENT_MESSAGE);
    }

    public function resetPasswordPage(Request $request, string $token)
    {
        $user = Password::getUser(['email' => (string) $request->query('email')]);

        if ($user === null || ! Password::tokenExists($user, $token)) {
            return to_route('expired-page');
        }

        return view('forgot-password.reset-password', ['token' => $token, 'email' => $user->email]);
    }

    public function resetPassword(Request $request, string $token)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation') + ['token' => $token],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return to_route('expired-page');
        }

        return to_route('login')->with('msg', 'Password berhasil diganti. Silakan login.');
    }
}
