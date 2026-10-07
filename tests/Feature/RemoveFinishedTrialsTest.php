<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RemoveFinishedTrialsTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_trial_students_with_two_sessions_leave_their_classes(): void
    {
        $classId = DB::table('class_transactions')->value('id');
        $done = Student::factory()->create(['Status' => 'trial', 'Quota' => 2]);
        $fresh = Student::factory()->create(['Status' => 'trial', 'Quota' => 1]);
        $active = Student::factory()->create(['Status' => 'aktif', 'Quota' => 5]);
        foreach ([$done, $fresh, $active] as $s) {
            DB::table('mapping_class_children')->insert([
                'class_id' => $classId,
                'student_id' => $s->id,
                'quota' => 0,
            ]);
        }

        $this->artisan('students:remove-finished-trials')->assertSuccessful();

        $this->assertFalse(DB::table('mapping_class_children')->where('student_id', $done->id)->exists());
        $this->assertTrue(DB::table('mapping_class_children')->where('student_id', $fresh->id)->exists());
        $this->assertTrue(DB::table('mapping_class_children')->where('student_id', $active->id)->exists());
    }

    public function test_it_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')->expectsOutputToContain('students:remove-finished-trials');
    }
}
