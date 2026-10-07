<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class StaffTestCase extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }

    /** Safe to call repeatedly in one test: resets session and guards so AuthenticateSession does not log the previous user's successor out. */
    protected function asRole(string $role): static
    {
        $this->flushSession();
        $this->app['auth']->forgetGuards();

        return $this->actingAs($this->user($role));
    }
}
