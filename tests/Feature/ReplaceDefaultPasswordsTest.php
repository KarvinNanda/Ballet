<?php

namespace Tests\Feature;

use App\Mail\ForgotPasswordEmail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ReplaceDefaultPasswordsTest extends TestCase
{
    use RefreshDatabase;

    private function legacyUser(): User
    {
        return User::factory()->create([
            'email' => 'old@example.com', 'role' => 'teacher', 'dob' => '1990-07-04',
            'password' => Hash::make('ballet04071990'),
        ]);
    }

    public function test_dry_run_lists_matches_and_changes_nothing(): void
    {
        Mail::fake();
        $user = $this->legacyUser();

        $this->artisan('users:replace-default-passwords')
            ->expectsOutputToContain('old@example.com')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('ballet04071990', $user->fresh()->password));
        Mail::assertNothingSent();
    }

    public function test_apply_replaces_the_password_and_sends_a_link(): void
    {
        Mail::fake();
        $user = $this->legacyUser();

        $this->artisan('users:replace-default-passwords --apply')->assertSuccessful();

        $this->assertFalse(Hash::check('ballet04071990', $user->fresh()->password));
        Mail::assertSent(ForgotPasswordEmail::class, fn ($m) => $m->hasTo('old@example.com') && $m->welcome);
    }

    public function test_seeded_accounts_with_real_passwords_are_left_alone(): void
    {
        Mail::fake();
        $this->artisan('users:replace-default-passwords --apply')->expectsOutputToContain('0 account')->assertSuccessful();
        Mail::assertNothingSent();
    }

    public function test_user_with_a_different_password_is_not_listed_or_changed(): void
    {
        Mail::fake();
        $user = User::factory()->create([
            'email' => 'safe@example.com', 'role' => 'teacher', 'dob' => '1990-07-04',
            'password' => Hash::make('something-else-entirely'),
        ]);

        $this->artisan('users:replace-default-passwords --apply')
            ->doesntExpectOutputToContain('safe@example.com')
            ->assertSuccessful();

        $this->assertTrue(Hash::check('something-else-entirely', $user->fresh()->password));
        Mail::assertNothingSent();
    }

    public function test_unreadable_dob_is_skipped_and_does_not_stop_the_run(): void
    {
        Mail::fake();
        $this->legacyUser();
        $bad = User::factory()->create(['email' => 'bad@example.com', 'role' => 'teacher', 'dob' => '1990-07-04']);
        // Bypass casts and strict mode to plant data a MySQL date column should not hold.
        DB::statement("SET SESSION sql_mode=''");
        DB::table('users')->where('id', $bad->id)->update(['dob' => 'not-a-date']);
        $empty = User::factory()->create(['email' => 'empty@example.com', 'role' => 'teacher', 'dob' => '1990-07-04']);
        DB::table('users')->where('id', $empty->id)->update(['dob' => '']);

        $this->artisan('users:replace-default-passwords')
            ->expectsOutputToContain('old@example.com')
            ->expectsOutputToContain('skipped (unreadable dob): bad@example.com')
            ->expectsOutputToContain('Dry run')
            ->assertSuccessful();
    }

    public function test_apply_continues_after_a_mail_failure_and_exits_non_zero(): void
    {
        $first = $this->legacyUser();
        $second = User::factory()->create([
            'email' => 'second@example.com', 'role' => 'teacher', 'dob' => '1990-07-04',
            'password' => Hash::make('ballet04071990'),
        ]);

        Mail::shouldReceive('to')->with('old@example.com')->andThrow(new \RuntimeException('smtp down'));
        $sent = \Mockery::mock();
        $sent->shouldReceive('send')->once();
        Mail::shouldReceive('to')->with('second@example.com')->andReturn($sent);

        $this->artisan('users:replace-default-passwords --apply')
            ->expectsOutputToContain('old@example.com: smtp down')
            ->expectsOutputToContain('Forgot password')
            ->assertFailed();

        $this->assertFalse(Hash::check('ballet04071990', $second->fresh()->password));
    }
}
