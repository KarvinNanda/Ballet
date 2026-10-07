<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SessionInvalidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_session_ends_when_password_changes_elsewhere(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->get('/profile')->assertOk();

        // Simulates a password reset done from another device.
        $user->forceFill(['password' => Hash::make('changed-elsewhere')])->save();

        $this->get('/profile')->assertRedirect('/login');
        $this->assertGuest();
    }
}
