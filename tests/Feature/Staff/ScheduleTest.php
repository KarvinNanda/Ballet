<?php

namespace Tests\Feature\Staff;

use App\Models\Schedule;
use Illuminate\Support\Carbon;

class ScheduleTest extends StaffTestCase
{
    public function test_clash_shows_a_message_and_stays_in_the_role(): void
    {
        $existing = Schedule::firstOrFail();
        $response = $this->asRole('head')->post(route('head.schedule.store', $existing->class_id), [
            'dateTime' => $existing->date,
        ]);
        $response->assertSessionHas('error');
        $this->assertStringStartsWith(url('/head/'), $response->headers->get('Location'));
    }

    public function test_unknown_schedule_edit_is_404(): void
    {
        $this->asRole('head')->get(route('head.schedule.edit', 999999))->assertNotFound();
    }

    public function test_index_lists_newest_first_and_head_sees_attendance(): void
    {
        $classId = Schedule::firstOrFail()->class_id;
        $this->asRole('head')->get(route('head.schedule.index', $classId))->assertOk()->assertSee('Attendance');
        $this->asRole('admin')->get(route('admin.schedule.index', $classId))->assertOk()->assertDontSee('/attendance', false);

        $newest = Carbon::parse(Schedule::where('class_id', $classId)->max('date'))->format('d M Y');
        $this->asRole('head')->get(route('head.schedule.index', $classId))->assertSee($newest);
    }

    public function test_admin_creates_updates_repeats_and_deletes_schedules(): void
    {
        $classId = Schedule::firstOrFail()->class_id;
        $this->asRole('admin');
        $this->get(route('admin.schedule.create', $classId))->assertOk();
        $this->get(route('admin.schedule.multiple.create', $classId))->assertOk()->assertSee('value="'.$classId.'" name="classId"', false);
        $this->get(route('admin.schedule.multiple.create'))->assertOk()->assertSee('<select class="form-select" id="classId"', false);
        $this->get('/admin/view/addMultipleSchedule/class?classId='.$classId)->assertRedirect(route('admin.schedule.multiple.create', $classId));

        $when = Carbon::parse('2031-03-03 10:00:00');
        $this->post(route('admin.schedule.store', $classId), ['dateTime' => $when->format('Y-m-d\TH:i')])
            ->assertRedirect(route('admin.schedule.index', $classId))->assertSessionHas('msg');
        $schedule = Schedule::where('class_id', $classId)->where('date', $when)->firstOrFail();

        $this->get(route('admin.schedule.edit', $schedule))->assertOk();
        $this->post(route('admin.schedule.update', $schedule), ['dateTime' => ''])->assertSessionHasErrors('dateTime');
        $this->post(route('admin.schedule.update', $schedule), ['dateTime' => '2031-03-04T11:30'])->assertRedirect(route('admin.schedule.index', $classId));
        $this->assertSame('2031-03-04 11:30:00', Carbon::parse($schedule->fresh()->date)->toDateTimeString());

        $this->post(route('admin.schedule.multiple.store'), ['classId' => $classId, 'dateTime' => '2032-01-05T09:00', 'ScheduleLoop' => 3])
            ->assertRedirect(route('admin.schedule.index', $classId));
        $this->assertSame(3, Schedule::where('class_id', $classId)->whereIn('date', ['2032-01-05 09:00:00', '2032-01-12 09:00:00', '2032-01-19 09:00:00'])->count());

        $this->post(route('admin.schedule.destroy', $schedule))->assertRedirect(route('admin.schedule.index', $classId));
        $this->assertNull($schedule->fresh());
    }
}
