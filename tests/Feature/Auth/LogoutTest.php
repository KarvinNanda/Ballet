<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_logout_via_get_is_not_allowed(): void
    {
        $this->actingAs(User::factory()->create())->get('/logout')->assertStatus(405);
        $this->assertAuthenticated();
    }

    public function test_logout_via_post_ends_the_session(): void
    {
        $this->actingAs(User::factory()->create());
        session()->put('marker', 'value');
        $csrfTokenBefore = session()->token();

        $this->post('/logout')->assertRedirect('/login');

        $this->assertGuest();
        $this->assertNull(session('marker'));
        $this->assertNotSame($csrfTokenBefore, session()->token());
    }
}
