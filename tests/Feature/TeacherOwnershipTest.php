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
        $this->post(route('addMultipleScheduleClassTeacher'), ['classId' => $classId, 'dateTime' => now()->addDay()->toDateTimeString(), 'ScheduleLoop' => 2])->assertForbidden();
        $this->post(route('deleteScheduleTeacher', ['id' => $this->otherSchedule->id, 'classId' => $classId]))->assertForbidden();
        $this->get(route('viewUpdateScheduleClassTeacher', ['scheduleId' => $this->otherSchedule->id]))->assertForbidden();
        $this->post(route('updateScheduleClassTeacher'), ['scheduleId' => $this->otherSchedule->id, 'dateTime' => now()->addDay()->toDateTimeString()])->assertForbidden();

        $this->assertSame($before, DB::table('schedules')->where('class_id', $classId)->count());
        $this->assertDatabaseHas('schedules', ['id' => $this->otherSchedule->id, 'date' => $this->otherSchedule->date]);
    }

    public function test_other_teachers_attendance_is_forbidden(): void
    {
        $this->actingAs($this->teacher);

        $this->post(route('viewAbsen', $this->otherSchedule->id))->assertForbidden();
        $this->post(route('getAbsen', $this->otherSchedule->id), ['student_id' => [], 'check' => [], 'keterangan' => []])->assertForbidden();
        $this->assertDatabaseMissing('header_absens', ['schedules_id' => $this->otherSchedule->id, 'teacher_id' => $this->teacher->id]);
    }

    public function test_own_route_id_with_foreign_class_id_in_body_cannot_add_a_schedule(): void
    {
        $own = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');
        $before = DB::table('schedules')->where('class_id', $this->otherClass->class_id)->count();

        $this->actingAs($this->teacher)->post(route('addScheduleClassTeacher', $own), [
            'classId' => $this->otherClass->class_id,
            'dateTime' => now()->addDays(3)->setTime(9, 0)->toDateTimeString(),
        ]);

        $this->assertSame($before, DB::table('schedules')->where('class_id', $this->otherClass->class_id)->count());
    }

    public function test_class_detail_lists_only_students_of_that_class(): void
    {
        $own = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');
        $foreignStudent = DB::table('students as st')
            ->join('mapping_class_children as c', 'c.student_id', 'st.id')
            ->whereNotIn('st.id', DB::table('mapping_class_children')->where('class_id', $own)->pluck('student_id'))
            ->value('st.LongName');

        $this->actingAs($this->teacher)->post(route('viewDetailTeacher', $own), ['id' => $own])
            ->assertOk()
            ->assertDontSee($foreignStudent);
    }

    public function test_schedule_overview_lists_only_own_classes(): void
    {
        $otherClassName = DB::table('class_transactions as ct')->join('class_types as t', 't.id', 'ct.class_type_id')
            ->where('ct.id', $this->otherClass->class_id)->value('t.class_name');
        $ownNames = DB::table('mapping_class_teachers as m')->join('class_transactions as ct', 'ct.id', 'm.class_id')
            ->join('class_types as t', 't.id', 'ct.class_type_id')->where('m.user_id', $this->teacher->id)->pluck('t.class_name');
        $this->assertNotContains($otherClassName, $ownNames->all(), 'pick a class name the teacher does not also teach');

        $this->actingAs($this->teacher)->get(route('viewAllScheduleTeacher', $this->teacher->id))
            ->assertOk()
            ->assertDontSee($otherClassName);
    }

    public function test_multiple_schedule_count_is_limited(): void
    {
        $own = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');

        $this->actingAs($this->teacher)->from(route('viewaddMultipleScheduleClass', $own))
            ->post(route('addMultipleScheduleClassTeacher'), ['classId' => $own, 'dateTime' => now()->addDay()->toDateTimeString(), 'ScheduleLoop' => 100000])
            ->assertSessionHasErrors('ScheduleLoop');
    }

    public function test_own_class_pages_still_work(): void
    {
        $own = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');
        $this->actingAs($this->teacher);

        $this->get(route('viewScheduleClassTeacher', $own))->assertOk();
        $this->get(route('viewAllScheduleTeacher', $this->teacher->id))->assertOk();
        $this->post(route('viewDetailTeacher', $own), ['id' => $own])->assertOk();
    }

    public function test_attended_schedule_cannot_be_redated_or_deleted(): void
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $classId = DB::table('mapping_class_teachers')->where('user_id', $teacher->id)->value('class_id');
        $schedule = \App\Models\Schedule::where('class_id', $classId)->firstOrFail();
        DB::table('header_absens')->insertOrIgnore(['schedules_id' => $schedule->id, 'teacher_id' => $teacher->id]);
        $date = $schedule->date;

        $this->actingAs($teacher)->post(route('updateScheduleClassTeacher'), ['scheduleId' => $schedule->id, 'dateTime' => '2030-01-01 10:00'])
            ->assertSessionHas('error');
        $this->post(route('deleteScheduleTeacher', [$schedule->id, $classId]))->assertSessionHas('error');

        $this->assertEquals($date, $schedule->fresh()->date);
    }

    public function test_invalid_attendance_redirects_to_the_schedule_list_not_a_post_only_url(): void
    {
        $classId = DB::table('mapping_class_teachers')->where('user_id', $this->teacher->id)->value('class_id');
        $schedule = \App\Models\Schedule::where('class_id', $classId)->firstOrFail();

        $this->actingAs($this->teacher)->from(route('viewAbsen', $schedule->id))
            ->post(route('getAbsen', $schedule->id), ['student_id' => [1], 'notes' => [str_repeat('x', 300)]])
            ->assertRedirect(route('viewScheduleClassTeacher', $classId))
            ->assertSessionHasErrors('notes.0')
            ->assertSessionHas('error');
        $this->get(route('viewScheduleClassTeacher', $classId))->assertOk();
    }
}
