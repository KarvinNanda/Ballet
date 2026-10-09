<?php

namespace Tests\Feature\Staff;

use App\Mail\ForgotPasswordEmail;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AccountInviteTest extends StaffTestCase
{
    private function payload(string $email): array
    {
        return [
            'inputName' => 'Baru', 'inputEmail' => $email, 'inputDate_of_Birth' => '1995-01-31',
            'inputAddress' => 'Jl. A', 'inputPhone' => '081234567890',
        ];
    }

    public static function creators(): array
    {
        return [['head.teacher.store'], ['head.finance.store'], ['AdminAdd']];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('creators')]
    public function test_new_account_gets_a_welcome_link_and_no_guessable_password(string $route): void
    {
        Mail::fake();
        $this->asRole('head')->post(route($route), $this->payload('baru@example.com'))->assertSessionHasNoErrors();

        $user = User::where('email', 'baru@example.com')->firstOrFail();
        $this->assertFalse(Hash::check('ballet31011995', $user->password));

        Mail::assertSent(ForgotPasswordEmail::class, function (ForgotPasswordEmail $mail) use ($user) {
            $html = $mail->render();

            return $mail->hasTo($user->email) && $mail->welcome
                && str_contains($html, 'reset/password/')
                && ! str_contains($html, 'ballet31011995');
        });
    }

    public function test_welcome_link_opens_the_reset_page(): void
    {
        Mail::fake();
        $this->asRole('head')->post(route('head.teacher.store'), $this->payload('baru@example.com'));

        $token = null;
        Mail::assertSent(ForgotPasswordEmail::class, function (ForgotPasswordEmail $mail) use (&$token) {
            $token = $mail->token;

            return true;
        });

        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->get(route('reset-password-page', ['token' => $token, 'email' => 'baru@example.com']))->assertOk();
    }
}
