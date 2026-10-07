<?php

namespace Tests\Feature\Auth;

use App\Mail\ForgotPasswordEmail;
use App\Models\User;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'known@example.com';
    private const SENT_MESSAGE = 'Jika email terdaftar, link reset sudah dikirim.';

    public function test_known_and_unknown_email_get_the_same_response(): void
    {
        Mail::fake();
        User::factory()->create(['email' => self::EMAIL]);

        $this->post('/forgot/password', ['email' => self::EMAIL])
            ->assertRedirect('/login')->assertSessionHas('msg', self::SENT_MESSAGE);
        $this->post('/forgot/password', ['email' => 'unknown@example.com'])
            ->assertRedirect('/login')->assertSessionHas('msg', self::SENT_MESSAGE);

        Mail::assertSent(ForgotPasswordEmail::class, 1);
    }

    public function test_reset_link_uses_app_url_not_the_request_host(): void
    {
        Mail::fake();
        User::factory()->create(['email' => self::EMAIL]);

        $this->post('http://evil.example/forgot/password', ['email' => self::EMAIL]);

        Mail::assertSent(ForgotPasswordEmail::class, function (ForgotPasswordEmail $mail) {
            $url = $mail->content()->with['url'];

            return str_starts_with($url, rtrim(config('app.url'), '/').'/reset/password/');
        });
    }

    public function test_reset_mail_is_sent_after_the_response(): void
    {
        Mail::fake();
        Bus::fake();
        User::factory()->create(['email' => self::EMAIL]);

        $this->post('/forgot/password', ['email' => self::EMAIL])->assertRedirect('/login');

        // Sending inside the request would make known emails slower than unknown ones (timing leak).
        Mail::assertNothingSent();
        Bus::assertDispatchedAfterResponse(CallQueuedClosure::class);
    }

    public function test_token_is_stored_hashed(): void
    {
        $token = $this->requestResetToken();

        $stored = DB::table('password_resets')->where('email', self::EMAIL)->value('token');

        $this->assertNotSame($token, $stored);
        $this->assertTrue(Hash::check($token, $stored));
    }

    public function test_reset_page_shows_the_form_for_a_valid_token(): void
    {
        $token = $this->requestResetToken();

        $this->get("/reset/password/{$token}?email=".urlencode(self::EMAIL))
            ->assertOk()
            ->assertSee('name="password"', false)
            ->assertSee('autocomplete="new-password"', false);
    }

    public function test_valid_token_resets_the_password_only_once(): void
    {
        $token = $this->requestResetToken();
        $rememberTokenBefore = User::where('email', self::EMAIL)->value('remember_token');

        $this->post("/reset/password/{$token}", $this->resetPayload('new-password-123'))
            ->assertRedirect('/login')
            ->assertSessionHas('msg', 'Password berhasil diganti. Silakan login.');

        $user = User::where('email', self::EMAIL)->first();
        $this->assertTrue(Hash::check('new-password-123', $user->password));
        $this->assertNotSame($rememberTokenBefore, $user->remember_token);

        $this->post("/reset/password/{$token}", $this->resetPayload('another-pass-456'))
            ->assertRedirect('/expired');
        $this->assertTrue(Hash::check('new-password-123', $user->fresh()->password));
    }

    public function test_expired_token_is_rejected(): void
    {
        $token = $this->requestResetToken();
        $this->travel(31)->minutes();

        $this->get("/reset/password/{$token}?email=".urlencode(self::EMAIL))->assertRedirect('/expired');
        $this->post("/reset/password/{$token}", $this->resetPayload('new-password-123'))->assertRedirect('/expired');
    }

    public function test_password_shorter_than_8_characters_is_rejected(): void
    {
        $token = $this->requestResetToken();

        $this->post("/reset/password/{$token}", $this->resetPayload('short7c'))
            ->assertSessionHasErrors('password');
    }

    public function test_reset_page_shows_an_email_error(): void
    {
        $token = $this->requestResetToken();
        $page = "/reset/password/{$token}?email=".urlencode(self::EMAIL);

        $this->from($page)->followingRedirects()
            ->post("/reset/password/{$token}", ['email' => 'not-an-email', 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])
            ->assertSee('email', false)
            ->assertSee('class="alert alert-danger', false);
    }

    public function test_expired_page_renders(): void
    {
        $this->get('/expired')->assertOk()->assertSee('Link tidak berlaku')->assertSee(route('email-page'), false);
    }

    private function requestResetToken(): string
    {
        Mail::fake();
        User::factory()->create(['email' => self::EMAIL]);
        $this->post('/forgot/password', ['email' => self::EMAIL]);

        $token = null;
        Mail::assertSent(ForgotPasswordEmail::class, function (ForgotPasswordEmail $mail) use (&$token) {
            $token = $mail->token;

            return true;
        });

        return $token;
    }

    private function resetPayload(string $password): array
    {
        return ['email' => self::EMAIL, 'password' => $password, 'password_confirmation' => $password];
    }
}
