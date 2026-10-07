<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class LoginEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [['admin'], ['head'], ['teacher'], ['finance']];
    }

    #[DataProvider('roles')]
    public function test_login_redirects_to_the_role_dashboard(string $role): void
    {
        $this->post('/login', ['email' => "{$role}@gmail.com", 'password' => "{$role}123"])
            ->assertRedirect("/{$role}");
    }

    public function test_user_without_a_role_is_logged_out_with_a_message(): void
    {
        User::factory()->create(['role' => null, 'email' => 'norole@example.com', 'password' => Hash::make('password-123')]);

        $this->from('/login')->followingRedirects()
            ->post('/login', ['email' => 'norole@example.com', 'password' => 'password-123'])
            ->assertOk()
            ->assertSee('Akun belum punya akses');

        $this->assertGuest();
    }

    public function test_home_with_a_role_without_dashboard_does_not_loop(): void
    {
        $this->actingAs(User::factory()->create(['role' => null]))
            ->get('/')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_one_ip_cannot_spray_many_accounts(): void
    {
        for ($i = 0; $i < 30; $i++) {
            $this->post('/login', ['email' => "user{$i}@example.com", 'password' => 'wrong-password']);
        }

        $this->from('/login')->followingRedirects()
            ->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin123'])
            ->assertSee('Terlalu banyak percobaan login.');

        $this->assertGuest();
    }

    public function test_behind_a_trusted_proxy_the_ip_limit_counts_the_real_client(): void
    {
        config(['app.trusted_proxies' => '127.0.0.1']);

        for ($i = 0; $i < 30; $i++) {
            $this->withHeader('X-Forwarded-For', '203.0.113.7')
                ->post('/login', ['email' => "user{$i}@example.com", 'password' => 'wrong-password']);
        }

        // Another visitor behind the same proxy is not locked out.
        $this->withHeader('X-Forwarded-For', '198.51.100.9')
            ->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin123'])
            ->assertRedirect('/admin');
    }

    public function test_forwarded_header_is_ignored_when_no_proxy_is_trusted(): void
    {
        config(['app.trusted_proxies' => null]);

        for ($i = 0; $i < 30; $i++) {
            $this->withHeader('X-Forwarded-For', "203.0.113.{$i}")
                ->post('/login', ['email' => "user{$i}@example.com", 'password' => 'wrong-password']);
        }

        // Spoofed X-Forwarded-For does not dodge the limit.
        $this->from('/login')->followingRedirects()
            ->withHeader('X-Forwarded-For', '198.51.100.9')
            ->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin123'])
            ->assertSee('Terlalu banyak percobaan login.');
    }

    public function test_logout_with_stale_token_while_logged_in_asks_to_try_again(): void
    {
        $this->enableCsrf();
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        $this->from('/admin')->post('/logout')
            ->assertRedirect('/admin')
            ->assertSessionHas('error', 'Sesi halaman sudah kedaluwarsa. Klik Sign Out sekali lagi.');

        $this->assertAuthenticated(); // a forged logout has no effect
    }

    public function test_logout_after_session_expired_goes_to_login(): void
    {
        $this->enableCsrf();

        $this->post('/logout')->assertRedirect(route('login'));
    }

    public function test_other_forms_without_csrf_token_are_still_rejected(): void
    {
        $this->enableCsrf();

        $this->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin123'])->assertStatus(419);
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        // TrustHosts/TrustProxies set Symfony's static trusted hosts/proxies; reset for the next test.
        Request::setTrustedHosts([]);
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);

        parent::tearDown();
    }

    /** Laravel skips CSRF checks while running unit tests; pretend we are not. */
    private function enableCsrf(): void
    {
        $this->app->instance('env', 'production');
    }
}
