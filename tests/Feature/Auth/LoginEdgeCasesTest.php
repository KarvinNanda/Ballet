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

    public function test_logout_without_csrf_token_does_not_log_out_and_shows_no_419(): void
    {
        $this->enableCsrf();
        $this->actingAs(User::where('role', 'admin')->firstOrFail());

        $this->post('/logout')->assertRedirect(route('login'));

        $this->assertAuthenticated();
    }

    public function test_other_forms_without_csrf_token_are_still_rejected(): void
    {
        $this->enableCsrf();

        $this->post('/login', ['email' => 'admin@gmail.com', 'password' => 'admin123'])->assertStatus(419);
        $this->assertGuest();
    }

    protected function tearDown(): void
    {
        // Running as "production" lets TrustHosts set Symfony's static trusted hosts; reset for the next test.
        Request::setTrustedHosts([]);

        parent::tearDown();
    }

    /** Laravel skips CSRF checks while running unit tests; pretend we are not. */
    private function enableCsrf(): void
    {
        $this->app->instance('env', 'production');
    }
}
