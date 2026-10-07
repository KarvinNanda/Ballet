<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    private const EMAIL = 'staff@example.com';
    private const PASSWORD = 'correct-password';

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create(['role' => 'admin', 'email' => self::EMAIL, 'password' => Hash::make(self::PASSWORD)]);
    }

    public function test_successful_login_regenerates_session_and_csrf_token(): void
    {
        $this->get('/login');
        $sessionIdBefore = session()->getId();
        $csrfTokenBefore = session()->token();

        $this->post('/login', ['email' => self::EMAIL, 'password' => self::PASSWORD])
            ->assertRedirect('/admin');

        $this->assertAuthenticated();
        $this->assertNotSame($sessionIdBefore, session()->getId());
        $this->assertNotSame($csrfTokenBefore, session()->token());
    }

    public function test_wrong_password_shows_generic_message(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => self::EMAIL, 'password' => 'wrong-password'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'Email atau password salah']);

        $this->assertGuest();
    }

    public function test_unknown_email_shows_the_same_generic_message(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'nobody@example.com', 'password' => 'whatever'])
            ->assertSessionHasErrors(['email' => 'Email atau password salah']);
    }

    public function test_invalid_email_format_is_rejected(): void
    {
        $this->from('/login')
            ->post('/login', ['email' => 'not-an-email', 'password' => 'x'])
            ->assertSessionHasErrors('email');
    }

    public function test_sixth_attempt_within_a_minute_is_throttled_even_with_correct_password(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => self::EMAIL, 'password' => 'wrong-password']);
        }

        $this->from('/login')->followingRedirects()
            ->post('/login', ['email' => self::EMAIL, 'password' => self::PASSWORD])
            ->assertSee('Terlalu banyak percobaan login.');

        $this->assertGuest();
    }

    public function test_successful_login_clears_failed_attempts(): void
    {
        for ($i = 0; $i < 4; $i++) {
            $this->post('/login', ['email' => self::EMAIL, 'password' => 'wrong-password']);
        }
        $this->post('/login', ['email' => self::EMAIL, 'password' => self::PASSWORD]);
        $this->post('/logout');

        $this->post('/login', ['email' => self::EMAIL, 'password' => 'wrong-password']);
        $this->post('/login', ['email' => self::EMAIL, 'password' => self::PASSWORD])->assertRedirect('/admin');
    }
}
