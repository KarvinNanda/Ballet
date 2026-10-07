<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Opening a page must never write data. Age refresh is an artisan command run by the scheduler. */
class StudentMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_active_link_redirects_and_writes_nothing(): void
    {
        $before = DB::table('mapping_class_children')->count();

        $this->actingAs($this->headUser())->get('/head/student/active')
            ->assertRedirect(route('headStudentPage', ['status' => 'aktif']));

        $this->assertSame($before, DB::table('mapping_class_children')->count());
    }

    public function test_old_non_active_link_redirects_and_writes_nothing(): void
    {
        DB::table('students')->update(['age' => 99]);

        $this->actingAs($this->headUser())->get('/head/student/non/active')
            ->assertRedirect(route('headStudentPage', ['status' => 'non-aktif']));

        $this->assertSame(0, DB::table('students')->where('age', '!=', 99)->count());
    }

    public function test_age_command_sets_age_from_date_of_birth_and_is_idempotent(): void
    {
        $id = DB::table('students')->value('id');
        DB::table('students')->where('id', $id)->update(['Dob' => now()->subYears(9)->subDays(10)->toDateString(), 'age' => 0]);

        $this->artisan('age:calculation')->assertSuccessful();
        $this->artisan('age:calculation')->assertSuccessful(); // running twice must not add a year

        $this->assertSame(9, (int) DB::table('students')->where('id', $id)->value('age'));
    }

    public function test_age_calculation_is_scheduled_daily_not_hourly(): void
    {
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => str_contains($e->command, 'age:calculation'));

        $this->assertNotNull($event);
        $this->assertSame('0 0 * * *', $event->expression);
    }

    private function headUser(): User
    {
        return User::where('role', 'head')->firstOrFail();
    }
}
