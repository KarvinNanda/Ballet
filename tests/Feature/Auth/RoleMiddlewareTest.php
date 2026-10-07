<?php

namespace Tests\Feature\Auth;

use PHPUnit\Framework\Attributes\DataProvider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public static function roles(): array
    {
        return [['admin'], ['head'], ['teacher'], ['finance']];
    }

    #[DataProvider('roles')]
    public function test_guest_is_redirected_to_login(string $role): void
    {
        $this->get("/{$role}")->assertRedirect('/login');
    }

    #[DataProvider('roles')]
    public function test_matching_role_sees_its_dashboard(string $role): void
    {
        $this->actingAs($this->userWithRole($role))->get("/{$role}")->assertOk();
    }

    #[DataProvider('roles')]
    public function test_other_role_is_forbidden(string $role): void
    {
        $other = $role === 'admin' ? 'head' : 'admin';

        $this->actingAs($this->userWithRole($other))->get("/{$role}")->assertForbidden();
    }

    private function userWithRole(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }
}
