<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_change_another_users_password(): void
    {
        $attacker = User::factory()->create(['role' => 'teacher']);
        $victim = User::factory()->create(['role' => 'head', 'password' => Hash::make('victim-password')]);

        $this->actingAs($attacker)->post("/password/{$victim->id}", [
            'new_password' => 'hacked-pass-1', 'confirm_password' => 'hacked-pass-1',
        ]);

        $this->assertTrue(Hash::check('victim-password', $victim->fresh()->password));
    }

    public function test_user_cannot_change_another_users_email(): void
    {
        $attacker = User::factory()->create(['role' => 'teacher']);
        $victim = User::factory()->create(['role' => 'head', 'email' => 'head@example.com']);

        $this->actingAs($attacker)->post("/profile/{$victim->id}", [
            'email' => 'attacker@example.com', 'address' => 'x', 'phone' => '081234567890',
        ]);

        $this->assertSame('head@example.com', $victim->fresh()->email);
    }

    public function test_own_password_change_works_and_keeps_the_session(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)->get('/password')->assertOk();

        $this->post('/password', ['new_password' => 'new-password-1', 'confirm_password' => 'new-password-1'])
            ->assertRedirect(route('change-password-page'));

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
        $this->get('/password')->assertOk();
        $this->assertAuthenticatedAs($user);
    }

    public function test_own_password_shorter_than_8_is_rejected(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/password', ['new_password' => 'short7c', 'confirm_password' => 'short7c'])
            ->assertSessionHasErrors('new_password');
    }

    public function test_profile_email_must_stay_unique(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/profile', [
            'email' => 'taken@example.com', 'address' => 'Jl. Test', 'phone' => '081234567890',
        ])->assertSessionHasErrors('email');
    }

    public function test_own_profile_update_works(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)->post('/profile', [
            'email' => 'me@example.com', 'address' => 'Jl. Baru', 'phone' => '081234567890',
        ])->assertRedirect(route('change-profile-page'));

        $this->assertSame('me@example.com', $user->fresh()->email);
    }
}
