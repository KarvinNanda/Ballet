<?php

namespace Tests\Feature\Staff;

use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class SchedulePagesTest extends StaffTestCase
{
    private function scheduledClassId(): int
    {
        return (int) DB::table('class_transactions')->where('is_freeze', 0)
            ->whereIn('id', DB::table('schedules')->pluck('class_id'))->orderBy('id')->value('id');
    }

    private function classWithoutSchedules(int $frozen = 0): int
    {
        return DB::table('class_transactions')->insertGetId([
            'class_type_id' => DB::table('class_types')->orderBy('id')->value('id'), 'Status' => 'aktif', 'is_freeze' => $frozen,
            'class_transaction_price' => 100000, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_list_title_names_course_and_teacher_and_links_back_to_the_class(): void
    {
        $classId = $this->scheduledClassId();

        $this->asRole('admin')->get(route('admin.schedule.index', $classId))->assertOk()
            ->assertSee('<h1 class="page-title">Schedule · '.e($this->expectedClassLabel($classId)).'</h1>', false)
            ->assertSee('href="'.route('admin.class.show', $classId).'"', false)
            ->assertSee('href="'.route('admin.schedule.create', $classId).'"', false)
            ->assertSee('href="'.route('admin.schedule.multiple.create', $classId).'"', false)
            ->assertSeeText('Add weekly schedules');
    }

    public function test_rows_show_day_date_and_time_with_attendance_and_a_confirmed_delete(): void
    {
        $schedule = Schedule::where('class_id', $this->scheduledClassId())->orderByDesc('date')->firstOrFail();
        $when = Carbon::parse($schedule->date);

        $html = $this->asRole('head')->get(route('head.schedule.index', $schedule->class_id))->assertOk()->getContent();
        $row = $this->rowFor($html, $when->format('d M Y'));

        $this->assertStringContainsString('<td>'.$when->format('l').'</td>', $row);
        $this->assertStringContainsString('<td>'.$when->format('H:i').'</td>', $row);
        $this->assertStringContainsString('href="'.route('head.schedule.edit', $schedule->id).'"', $row);
        $this->assertStringContainsString('href="'.route('head.attendance.edit', $schedule->id).'"', $row);
        $this->assertStringContainsString('data-confirm="Delete the session of '.$when->format('d M Y, H:i').'? This cannot be undone."', $row);
    }

    public function test_class_without_schedules_shows_the_empty_state_and_the_course_name(): void
    {
        $classId = $this->classWithoutSchedules();
        $course = DB::table('class_types')->orderBy('id')->value('class_name');

        $this->asRole('admin')->get(route('admin.schedule.index', $classId))->assertOk()
            ->assertSee('<h1 class="page-title">Schedule · '.e($course).'</h1>', false)
            ->assertSeeText('No schedules yet')
            ->assertDontSee('<table', false);
    }

    public function test_frozen_class_links_back_to_the_frozen_detail_page(): void
    {
        $classId = $this->classWithoutSchedules(frozen: 1);

        $this->asRole('head')->get(route('head.schedule.index', $classId))->assertOk()
            ->assertSee('href="'.route('head.class.freeze.show', $classId).'"', false);
    }

    public function test_add_page_names_the_class(): void
    {
        $classId = $this->scheduledClassId();

        $this->asRole('admin')->get(route('admin.schedule.create', $classId))->assertOk()
            ->assertSee('<h1 class="page-title">Add schedule</h1>', false)
            ->assertSeeText($this->expectedClassLabel($classId))
            ->assertSee('<input type="datetime-local" id="field-dateTime" name="dateTime" value="" class="form-control" required', false);
    }

    public function test_update_form_shows_the_stored_date_and_time(): void
    {
        $schedule = Schedule::firstOrFail();

        $this->asRole('admin')->get(route('admin.schedule.edit', $schedule))->assertOk()
            ->assertSee('<h1 class="page-title">Update schedule</h1>', false)
            ->assertSeeText($this->expectedClassLabel($schedule->class_id))
            ->assertSee('name="dateTime" value="'.Carbon::parse($schedule->date)->format('Y-m-d\TH:i').'"', false);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_the_date(): void
    {
        $schedule = Schedule::firstOrFail();
        $before = Carbon::parse($schedule->date)->format('Y-m-d H:i');

        $update = route('head.schedule.update', $schedule);
        $html = $this->asRole('head')->get(route('head.schedule.edit', $schedule))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))
            ->assertSessionHasNoErrors()->assertRedirect(route('head.schedule.index', $schedule->class_id));

        $this->assertSame($before, Carbon::parse($schedule->fresh()->date)->format('Y-m-d H:i'));
    }

    public function test_weekly_page_explains_the_number_of_weeks(): void
    {
        $classId = $this->scheduledClassId();

        $this->asRole('admin')->get(route('admin.schedule.multiple.create', $classId))->assertOk()
            ->assertSee('<h1 class="page-title">Add weekly schedules</h1>', false)
            ->assertSeeText($this->expectedClassLabel($classId))
            ->assertSee('<input type="number" id="field-ScheduleLoop" name="ScheduleLoop" value="" class="form-control" min="1" max="52" required', false)
            ->assertSeeText('Number of weeks')
            ->assertSeeText('Creates one schedule per week, starting from the first session.');
    }
}
