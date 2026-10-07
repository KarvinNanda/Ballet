<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    /** Per IP, across all emails, so one address cannot try one password on many accounts. */
    private const MAX_ATTEMPTS_PER_IP = 30;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());
            RateLimiter::hit($this->ipThrottleKey());

            throw ValidationException::withMessages(['email' => 'Email atau password salah']);
        }

        RateLimiter::clear($this->throttleKey());

        // A valid account without a dashboard (role empty or unknown) would loop on "/".
        if (! Route::has((string) Auth::user()->role)) {
            Auth::logout();

            throw ValidationException::withMessages(['email' => 'Akun belum punya akses. Hubungi admin.']);
        }
    }

    private function ensureIsNotRateLimited(): void
    {
        $limited = collect([
            $this->throttleKey() => self::MAX_ATTEMPTS,
            $this->ipThrottleKey() => self::MAX_ATTEMPTS_PER_IP,
        ])->filter(fn ($max, $key) => RateLimiter::tooManyAttempts($key, $max));

        if ($limited->isEmpty()) {
            return;
        }

        $seconds = $limited->keys()->map(fn ($key) => RateLimiter::availableIn($key))->max();

        throw ValidationException::withMessages([
            'email' => "Terlalu banyak percobaan login. Coba lagi dalam {$seconds} detik.",
        ]);
    }

    private function throttleKey(): string
    {
        return Str::lower((string) $this->input('email')).'|'.$this->ip();
    }

    private function ipThrottleKey(): string
    {
        return 'login-ip|'.$this->ip();
    }
}
