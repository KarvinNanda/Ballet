<?php

namespace Tests\Feature\Teacher;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendanceWindowRoutesTest extends TeacherTestCase
{
    private const NOT_OPEN = 'Attendance for this session is not open.';

    /** @return array{0: User, 1: int} a teacher and one of their classes with two active students */
    private function teacherAndClass(): array
    {
        $teacher = $this->newTeacher();

        return [$teacher, $this->classFor($teacher)];
    }

    /** @return array<int, int> student id => Quota */
    private function quotas(int $classId): array
    {
        return $this->studentsOf($classId)->pluck('Quota', 'id')->all();
    }

    public function test_the_form_does_not_open_before_the_session_starts(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-09 16:00:00');
        $this->at('2026-10-09 15:59:00');

        $this->asTeacher($teacher)->from(route('teacher'))->post(route('viewAbsen', $schedule))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('error', self::NOT_OPEN);
    }

    public function test_the_form_is_open_from_the_start_until_the_end_of_the_next_day(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-09 16:00:00');

        foreach (['2026-10-09 16:00:00', '2026-10-10 23:59:00'] as $now) {
            $this->at($now);
            $this->asTeacher($teacher)->post(route('viewAbsen', $schedule))->assertOk();
        }

        $this->at('2026-10-11 00:00:00');
        $this->asTeacher($teacher)->from(route('teacher'))->post(route('viewAbsen', $schedule))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('error', self::NOT_OPEN);
    }

    public function test_saving_before_the_start_or_after_the_window_is_refused_without_side_effects(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $notStarted = $this->sessionAt($classId, '2026-10-09 16:00:00');
        $closed = $this->sessionAt($classId, '2026-10-07 16:00:00');
        $quotas = $this->quotas($classId);
        $this->at('2026-10-09 10:00:00');

        foreach ([$notStarted, $closed] as $schedule) {
            $this->asTeacher($teacher)->post(route('getAbsen', $schedule), $this->everyonePresent($classId, route('teacher')))
                ->assertRedirect(route('teacher'))
                ->assertSessionHas('error', self::NOT_OPEN);
            $this->assertDatabaseMissing('header_absens', ['schedules_id' => $schedule]);
        }
        $this->assertSame($quotas, $this->quotas($classId));
    }

    public function test_a_form_opened_in_time_cannot_be_saved_after_the_window_closes(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-09 16:00:00');
        $quotas = $this->quotas($classId);

        $this->at('2026-10-10 23:50:00');
        $this->asTeacher($teacher)->post(route('viewAbsen', $schedule))->assertOk();

        $this->at('2026-10-11 00:05:00');
        $this->post(route('getAbsen', $schedule), $this->everyonePresent($classId, route('teacher')))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('error', self::NOT_OPEN);

        $this->assertDatabaseMissing('header_absens', ['schedules_id' => $schedule]);
        $this->assertSame($quotas, $this->quotas($classId));
    }

    public function test_saving_inside_the_window_records_the_session_once(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-09 16:00:00');
        $quotas = $this->quotas($classId);
        $this->at('2026-10-10 20:00:00');

        $this->asTeacher($teacher)->post(route('getAbsen', $schedule), $this->everyonePresent($classId, route('teacher')))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('msg', 'Success Making Attendance');

        $this->assertSame(1, DB::table('header_absens')->where('schedules_id', $schedule)->count());
        $this->assertSame(array_map(fn (int $quota) => $quota + 1, $quotas), $this->quotas($classId));
    }

    public function test_a_recorded_session_can_be_viewed_at_any_time(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-01 16:00:00');
        $this->recordRaw($schedule, $this->studentsOf($classId)->mapWithKeys(fn ($s) => [$s->id => ['Attend', '-']])->all());
        $this->at('2026-12-01 10:00:00');

        $this->asTeacher($teacher)->post(route('viewAbsen', $schedule))->assertOk();
    }

    public function test_the_window_follows_jakarta_midnight_not_utc(): void
    {
        [$teacher, $classId] = $this->teacherAndClass();
        $twoDaysAgo = $this->sessionAt($classId, '2026-10-08 16:00:00');
        $justAfterMidnight = $this->sessionAt($classId, '2026-10-10 00:15:00');

        // 00:30 in Jakarta is 17:30 UTC on the day before: a UTC clock still says 2026-10-09.
        $this->at('2026-10-10 00:30:00');
        $this->assertSame('2026-10-09 17:30', now()->utc()->format('Y-m-d H:i'));

        $this->asTeacher($teacher)->from(route('teacher'))->post(route('viewAbsen', $twoDaysAgo))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('error', self::NOT_OPEN);
        $this->asTeacher($teacher)->post(route('viewAbsen', $justAfterMidnight))->assertOk();

        $this->asTeacher($teacher)->post(route('getAbsen', $twoDaysAgo), $this->everyonePresent($classId, route('teacher')))
            ->assertSessionHas('error', self::NOT_OPEN);
        $this->asTeacher($teacher)->post(route('getAbsen', $justAfterMidnight), $this->everyonePresent($classId, route('teacher')))
            ->assertSessionHas('msg', 'Success Making Attendance');

        $this->assertDatabaseMissing('header_absens', ['schedules_id' => $twoDaysAgo]);
        $this->assertDatabaseHas('header_absens', ['schedules_id' => $justAfterMidnight]);
    }

    public function test_another_teachers_session_outside_the_window_is_still_forbidden(): void
    {
        [, $classId] = $this->teacherAndClass();
        $schedule = $this->sessionAt($classId, '2026-10-01 16:00:00');
        $intruder = $this->newTeacher('Intruder Teacher');
        $this->at('2026-10-09 10:00:00');

        $this->asTeacher($intruder)->post(route('viewAbsen', $schedule))->assertForbidden();
        $this->asTeacher($intruder)->post(route('getAbsen', $schedule), $this->everyonePresent($classId, route('teacher')))
            ->assertForbidden();

        $this->assertDatabaseMissing('header_absens', ['schedules_id' => $schedule]);
    }
}
