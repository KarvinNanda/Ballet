<?php

namespace Tests\Feature\Teacher;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * A teacher cannot schedule into the past nor move a session that has already started.
 * The clock is frozen at 10:00 Jakarta (03:00 UTC), so a wrong-zone comparison shows up as a 7 hour error.
 */
class SchedulePastDatesTest extends TeacherTestCase
{
    private const STARTED_MESSAGE = 'This session has already started; ask the head to change it.';

    private User $teacher;
    private int $classId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = $this->newTeacher('Past Dates Teacher');
        $this->classId = $this->classFor($this->teacher, 'Grade 1', 1);
        $this->at('2026-10-09 10:00:00');
    }

    private function add(string $dateTime)
    {
        return $this->asTeacher($this->teacher)->post(route('addScheduleClassTeacher', $this->classId), ['dateTime' => $dateTime]);
    }

    private function update(int $scheduleId, string $dateTime)
    {
        return $this->asTeacher($this->teacher)->post(route('updateScheduleClassTeacher'), ['scheduleId' => $scheduleId, 'dateTime' => $dateTime]);
    }

    private function sessions(): int
    {
        return DB::table('schedules')->where('class_id', $this->classId)->count();
    }

    private function dateOf(int $scheduleId): string
    {
        return (string) DB::table('schedules')->where('id', $scheduleId)->value('date');
    }

    // --- add ---

    public function test_add_in_the_past_is_refused(): void
    {
        $this->add('2026-10-08T10:00')->assertSessionHasErrors(['dateTime' => 'Choose a date and time that has not passed yet.']);

        $this->assertSame(0, $this->sessions());
    }

    public function test_add_earlier_today_in_jakarta_is_refused_although_it_is_ahead_of_the_utc_clock(): void
    {
        // 08:00 Jakarta is 01:00 UTC, but the app clock reads 03:00 UTC; a naive compare of the digits (08:00 > 03:00) would accept it.
        $this->add('2026-10-09T08:00')->assertSessionHasErrors('dateTime');

        $this->assertSame(0, $this->sessions());
    }

    public function test_add_later_today_in_jakarta_is_accepted(): void
    {
        // 11:00 Jakarta is one hour ahead of now (10:00 Jakarta).
        $this->add('2026-10-09T11:00')->assertSessionHasNoErrors()->assertRedirect(route('viewScheduleClassTeacher', $this->classId));

        $this->assertSame('2026-10-09 11:00:00', DB::table('schedules')->where('class_id', $this->classId)->value('date'));
    }

    public function test_add_one_hour_ahead_is_accepted_and_the_current_minute_is_too(): void
    {
        $this->add('2026-10-09T11:00')->assertSessionHasNoErrors();
        $this->add('2026-10-09T10:00')->assertSessionHasNoErrors(); // exactly now (minute precision)

        $this->assertSame(2, $this->sessions());
    }

    public function test_add_one_minute_ago_is_refused(): void
    {
        $this->at('2026-10-09 10:00:30');

        $this->add('2026-10-09T09:59')->assertSessionHasErrors('dateTime');
        $this->add('2026-10-09T10:00')->assertSessionHasNoErrors(); // same minute as now: datetime-local cannot say seconds
    }

    // --- weekly ---

    public function test_weekly_with_a_past_first_session_is_refused_and_creates_nothing(): void
    {
        $this->asTeacher($this->teacher)
            ->post(route('addMultipleScheduleClassTeacher'), ['classId' => $this->classId, 'dateTime' => '2026-10-08T10:00', 'ScheduleLoop' => 3])
            ->assertSessionHasErrors(['dateTime' => 'Choose a date and time that has not passed yet.']);

        $this->assertSame(0, $this->sessions());
    }

    public function test_weekly_starting_ahead_is_created(): void
    {
        $this->asTeacher($this->teacher)
            ->post(route('addMultipleScheduleClassTeacher'), ['classId' => $this->classId, 'dateTime' => '2026-10-09T11:00', 'ScheduleLoop' => 3])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $this->sessions());
    }

    // --- update ---

    public function test_update_of_an_upcoming_session_to_a_future_time_is_saved(): void
    {
        $id = $this->sessionAt($this->classId, '2026-10-16 16:00:00');

        $this->update($id, '2026-10-17T09:30')->assertSessionHasNoErrors()->assertRedirect(route('viewScheduleClassTeacher', $this->classId));

        $this->assertSame('2026-10-17 09:30:00', $this->dateOf($id));
    }

    public function test_update_of_an_upcoming_session_to_a_past_time_is_refused(): void
    {
        $id = $this->sessionAt($this->classId, '2026-10-16 16:00:00');

        $this->update($id, '2026-10-09T08:00')->assertSessionHasErrors('dateTime');
        $this->update($id, '2026-10-01T16:00')->assertSessionHasErrors('dateTime');

        $this->assertSame('2026-10-16 16:00:00', $this->dateOf($id));
    }

    public function test_update_of_a_session_that_has_already_started_is_refused(): void
    {
        $open = $this->sessionAt($this->classId, '2026-10-08 16:00:00');    // inside the window
        $missed = $this->sessionAt($this->classId, '2026-10-01 16:00:00');  // window closed
        $justStarted = $this->sessionAt($this->classId, '2026-10-09 10:00:00'); // starts exactly now

        foreach ([$open, $missed, $justStarted] as $id) {
            $before = $this->dateOf($id);
            $this->update($id, '2026-10-20T16:00')->assertSessionHas('error', self::STARTED_MESSAGE);
            $this->assertSame($before, $this->dateOf($id));
        }
    }

    public function test_a_recorded_session_still_gets_the_recorded_refusal_first(): void
    {
        $id = $this->sessionAt($this->classId, '2026-10-01 16:00:00');
        $this->recordRaw($id);

        $this->update($id, '2026-10-20T16:00')->assertSessionHas('error', 'Jadwal ini sudah diabsen, jadi tidak bisa diubah lagi.');
    }

    public function test_the_update_form_does_not_open_for_a_started_session(): void
    {
        $open = $this->sessionAt($this->classId, '2026-10-08 16:00:00');
        $missed = $this->sessionAt($this->classId, '2026-10-01 16:00:00');

        foreach ([$open, $missed] as $id) {
            $this->asTeacher($this->teacher)->get(route('viewUpdateScheduleClassTeacher', ['scheduleId' => $id]))
                ->assertRedirect()
                ->assertSessionHas('error', self::STARTED_MESSAGE);
        }
    }

    public function test_the_update_form_opens_for_an_upcoming_session(): void
    {
        $id = $this->sessionAt($this->classId, '2026-10-16 16:00:00');

        $this->asTeacher($this->teacher)->get(route('viewUpdateScheduleClassTeacher', ['scheduleId' => $id]))->assertOk();
    }

    public function test_the_schedule_page_offers_update_only_for_upcoming_sessions_and_delete_for_every_unrecorded_one(): void
    {
        $upcoming = $this->sessionAt($this->classId, '2026-10-16 16:00:00');
        $open = $this->sessionAt($this->classId, '2026-10-08 16:00:00');
        $missed = $this->sessionAt($this->classId, '2026-10-01 16:00:00');
        $recorded = $this->sessionAt($this->classId, '2026-10-02 16:00:00');
        $this->recordRaw($recorded);

        $html = $this->asTeacher($this->teacher)->get(route('viewScheduleClassTeacher', $this->classId))->assertOk()->getContent();

        $row = $this->rowFor($html, '16 Oct 2026');
        $this->assertStringContainsString('>Update</a>', $row);
        $this->assertStringContainsString('Delete…', $row);

        foreach (['08 Oct 2026', '01 Oct 2026'] as $date) {
            $row = $this->rowFor($html, $date);
            $this->assertStringNotContainsString('Update', $row, $date);
            $this->assertStringContainsString('Delete…', $row, $date);
        }

        $row = $this->rowFor($html, '02 Oct 2026');
        $this->assertStringNotContainsString('Update', $row);
        $this->assertStringNotContainsString('Delete', $row);
    }

    // --- staff keeps today's behaviour ---

    public function test_staff_can_still_add_a_schedule_in_the_past(): void
    {
        $this->asRole('head')
            ->post(route('head.schedule.store', $this->classId), ['dateTime' => '2026-10-01T10:00'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('msg');

        $this->assertSame('2026-10-01 10:00:00', DB::table('schedules')->where('class_id', $this->classId)->value('date'));
    }

    public function test_every_teacher_date_field_starts_at_the_current_jakarta_minute(): void
    {
        // Frozen at 10:00 Jakarta = 03:00 UTC; the picker must not offer earlier times (the server refuses them anyway).
        $upcoming = $this->sessionAt($this->classId, '2026-10-16 16:00:00');
        $pages = [
            route('viewaddScheduleClass', $this->classId),
            route('viewaddMultipleScheduleClass', $this->classId),
            route('viewUpdateScheduleClassTeacher', ['scheduleId' => $upcoming]),
        ];

        foreach ($pages as $url) {
            $this->asTeacher($this->teacher)->get($url)->assertOk()
                ->assertSee('name="dateTime"', false)
                ->assertSee('min="2026-10-09T10:00"', false);
        }
    }
}
