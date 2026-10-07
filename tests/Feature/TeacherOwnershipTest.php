<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** A teacher may only see and change classes, schedules and attendance of classes they teach. */
class TeacherOwnershipTest extends TestCase
{
    use RefreshDatabase;

    private User $teacher;
    private object $otherClass;
    private object $otherSchedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $this->otherClass = DB::table('mapping_class_teachers')->where('user_id', '!=', $this->teacher->id)->first();
        $this->otherSchedule = DB::table('schedules')->where('class_id', $this->otherClass->class_id)->first();
    }

    public function test_other_teachers_class_pages_are_forbidden(): void
    {
        $classId = $this->otherClass->class_id;
        $this->actingAs($this->teacher);

        $this->get(route('viewScheduleClassTeacher', $classId))->assertForbidden();
        $this->get(route('viewaddScheduleClass', $classId))->assertForbidden();
        $this->get(route('viewaddMultipleScheduleClass', $classId))->assertForbidden();
        $this->post(route('viewDetailTeacher', $classId), ['id' => $classId])->assertForbidden();
        $this->get(route('viewAllScheduleTeacher', $this->otherClass->user_id))->assertForbidden();
    }

    public function test_other_teachers_schedules_cannot_be_changed(): void
    {
        $classId = $this->otherClass->class_id;
        $before = DB::table('schedules')->where('class_id', $classId)->count();
        $this->actingAs($this->teacher);

        $this->post(route('addScheduleClassTeacher', $classId), ['dateTime' => now()->addDays(3)->toDateTimeString()])->assertForbidden();
        $this->post(route('addMultipleScheduleClassTeacher'), ['classId' => $classId, 'dateTime' => now()->toDateTimeString(), 'ScheduleLoop' => 2])->assertForbidden();
        $this->post(route('deleteScheduleTeacher', ['id' => $this->otherSchedule->id, 'classId' => $classId]))->assertForbidden();
        $this->get(route('viewUpdateScheduleClassTeacher', ['scheduleId' => $this->otherSchedule->id]))->assertForbidden();
        $this->post(route('updateScheduleClassTeacher'), ['scheduleId' => $this->otherSchedule->id, 'dateTime' => now()->toDateTimeString()])->assertForbidden();

        $this->assertSame($before, DB::table('schedules')->where('class_id', $classId)->count());
        $this->assertDatabaseHas('schedules', ['id' => $this->otherSchedule->id, 'date' => $this->otherSchedule->date]);
    }

    public function test_other_teachers_attendance_is_forbidden(): void
    {
        $this->actingAs($this->teacher);

        $this->post(route('viewAbsen', $this->otherSchedule->id))->assertForbidden();
        $this->post(route('getAbsen', $this->otherSchedule->id), ['nis' => [], 'check' => [], 'keterangan' => []])->assertForbidden();
        $this->assertDatabaseMissing('header_absens', ['schedules_id' => $this->otherSchedule->id, 'teacher_id' => $this->teacher->id]);
    }

    public function test_own_class_pages_still_work(): void
    {
        $own = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');
        $this->actingAs($this->teacher);

        $this->get(route('viewScheduleClassTeacher', $own))->assertOk();
        $this->get(route('viewAllScheduleTeacher', $this->teacher->id))->assertOk();
        $this->post(route('viewDetailTeacher', $own), ['id' => $own])->assertOk();
    }
}
