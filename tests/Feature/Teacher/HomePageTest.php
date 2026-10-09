<?php

namespace Tests\Feature\Teacher;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class HomePageTest extends TeacherTestCase
{
    private function home(User $teacher, array $query = []): string
    {
        return $this->asTeacher($teacher)->get(route('teacher', $query))->assertOk()->getContent();
    }

    /** @return array{0: string, 1: string} the page split at the "Still open from yesterday" heading (second part empty when absent) */
    private function sections(string $html): array
    {
        $parts = explode('Still open from yesterday', $html, 2);

        return [$parts[0], $parts[1] ?? ''];
    }

    public function test_todays_sessions_are_listed_by_time_with_status_and_action(): void
    {
        $teacher = $this->newTeacher();
        $classId = $this->classFor($teacher, 'Grade 1', 2);
        $this->enrol($classId, Student::factory()->create(['Status' => 'non-aktif']));
        $recorded = $this->sessionAt($classId, '2026-10-09 08:00:00');
        $this->recordRaw($recorded);
        $open = $this->sessionAt($classId, '2026-10-09 09:00:00');
        $later = $this->sessionAt($classId, '2026-10-09 16:00:00');
        $this->at('2026-10-09 10:00:00');

        $html = $this->home($teacher);

        $this->assertStringContainsString('<h1 class="page-title">Today</h1>', $html);
        $this->assertStringContainsString('<p class="page-subtitle">Friday, 09 Oct 2026</p>', $html);
        $this->assertStringContainsString("Today's sessions", $html);
        $this->assertStringContainsString('name="keyword"', $html);

        $openRow = $this->rowFor($html, '09:00');
        $this->assertStringContainsString('<td>Grade 1</td>', $openRow);
        $this->assertStringContainsString('<td>2</td>', $openRow);
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Open</span>', $openRow);
        $this->assertStringContainsString('action="'.route('viewAbsen', $open).'"', $openRow);
        $this->assertStringContainsString('Take attendance', $openRow);

        $recordedRow = $this->rowFor($html, '08:00');
        $this->assertStringContainsString('<span class="status-badge status-badge-success">Recorded</span>', $recordedRow);
        $this->assertStringContainsString('action="'.route('viewAbsen', $recorded).'"', $recordedRow);
        $this->assertStringContainsString('View attendance', $recordedRow);

        $laterRow = $this->rowFor($html, '16:00');
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Not started</span>', $laterRow);
        $this->assertStringNotContainsString('<form', $laterRow);
        $this->assertStringNotContainsString(route('viewAbsen', $later).'"', $html);

        $this->assertLessThan(strpos($html, '<td>09:00</td>'), strpos($html, '<td>08:00</td>'));
        $this->assertLessThan(strpos($html, '<td>16:00</td>'), strpos($html, '<td>09:00</td>'));
        $this->assertStringNotContainsString('Still open from yesterday', $html);
    }

    public function test_yesterdays_unrecorded_sessions_stay_open_and_others_are_not_listed(): void
    {
        $teacher = $this->newTeacher();
        $classId = $this->classFor($teacher);
        $stillOpen = $this->sessionAt($classId, '2026-10-08 16:00:00');
        $doneYesterday = $this->sessionAt($classId, '2026-10-08 17:00:00');
        $this->recordRaw($doneYesterday);
        $missed = $this->sessionAt($classId, '2026-10-07 16:00:00');
        $tomorrow = $this->sessionAt($classId, '2026-10-10 09:00:00');
        $this->at('2026-10-09 10:00:00');

        [$today, $yesterday] = $this->sections($this->home($teacher));

        $this->assertStringContainsString('No sessions today', $today);
        $row = $this->rowFor($yesterday, '16:00');
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Open</span>', $row);
        $this->assertStringContainsString('action="'.route('viewAbsen', $stillOpen).'"', $row);
        $this->assertStringContainsString('Take attendance', $row);
        foreach ([$doneYesterday, $missed, $tomorrow] as $hidden) {
            $this->assertStringNotContainsString(route('viewAbsen', $hidden).'"', $today.$yesterday);
        }
    }

    public function test_another_teachers_sessions_never_appear(): void
    {
        $teacher = $this->newTeacher('Own Teacher');
        $own = $this->classFor($teacher, 'Grade 1');
        $ownToday = $this->sessionAt($own, '2026-10-09 09:00:00');
        $ownYesterday = $this->sessionAt($own, '2026-10-08 16:00:00');

        $other = $this->newTeacher('Other Teacher');
        $foreign = $this->classFor($other, 'Grade 2');
        $foreignToday = $this->sessionAt($foreign, '2026-10-09 09:30:00');
        $foreignYesterday = $this->sessionAt($foreign, '2026-10-08 15:00:00');
        // The other teacher also teaches the own class: its sessions must still be listed once.
        DB::table('mapping_class_teachers')->insert(['class_id' => $own, 'user_id' => $other->id, 'created_at' => now(), 'updated_at' => now()]);
        $this->at('2026-10-09 10:00:00');

        $html = $this->home($teacher);

        $this->assertSame(1, substr_count($html, 'action="'.route('viewAbsen', $ownToday).'"'));
        $this->assertSame(1, substr_count($html, 'action="'.route('viewAbsen', $ownYesterday).'"'));
        $this->assertStringNotContainsString(route('viewAbsen', $foreignToday).'"', $html);
        $this->assertStringNotContainsString(route('viewAbsen', $foreignYesterday).'"', $html);
        $this->assertStringNotContainsString('Grade 2', $html);
    }

    public function test_inactive_classes_are_not_listed(): void
    {
        $teacher = $this->newTeacher();
        $inactive = $this->classFor($teacher, 'Grade 3', 1, 'non-aktif');
        $session = $this->sessionAt($inactive, '2026-10-09 09:00:00');
        $this->at('2026-10-09 10:00:00');

        $html = $this->home($teacher);

        $this->assertStringNotContainsString(route('viewAbsen', $session).'"', $html);
        $this->assertStringContainsString('No sessions today', $html);
    }

    public function test_just_after_jakarta_midnight_today_and_yesterday_follow_jakarta_dates(): void
    {
        $teacher = $this->newTeacher();
        $classId = $this->classFor($teacher);
        $afterMidnight = $this->sessionAt($classId, '2026-10-10 00:15:00');
        $lateYesterday = $this->sessionAt($classId, '2026-10-09 23:00:00');
        $twoDaysAgo = $this->sessionAt($classId, '2026-10-08 23:00:00');
        // 2026-10-10 00:30 in Jakarta is still 2026-10-09 in UTC.
        $this->at('2026-10-10 00:30:00');

        $html = $this->home($teacher);
        [$today, $yesterday] = $this->sections($html);

        $this->assertStringContainsString('<p class="page-subtitle">Saturday, 10 Oct 2026</p>', $html);
        $this->assertStringContainsString('action="'.route('viewAbsen', $afterMidnight).'"', $today);
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Open</span>', $this->rowFor($today, '00:15'));
        $this->assertStringContainsString('action="'.route('viewAbsen', $lateYesterday).'"', $yesterday);
        $this->assertStringNotContainsString(route('viewAbsen', $twoDaysAgo).'"', $html);
    }

    public function test_search_matches_the_class_or_a_student_name(): void
    {
        $teacher = $this->newTeacher();
        $grade1 = $this->classFor($teacher, 'Grade 1', 0);
        $this->enrol($grade1, Student::factory()->create(['LongName' => 'Kirana Search']));
        $grade2 = $this->classFor($teacher, 'Grade 2', 1);
        $first = $this->sessionAt($grade1, '2026-10-09 09:00:00');
        $second = $this->sessionAt($grade2, '2026-10-09 09:30:00');
        $yesterday = $this->sessionAt($grade2, '2026-10-08 16:00:00');
        $this->at('2026-10-09 10:00:00');

        $byStudent = $this->home($teacher, ['keyword' => 'Kirana']);
        $this->assertStringContainsString('name="keyword" value="Kirana"', $byStudent);
        $this->assertStringContainsString(route('viewAbsen', $first).'"', $byStudent);
        $this->assertStringNotContainsString(route('viewAbsen', $second).'"', $byStudent);
        $this->assertStringNotContainsString('Still open from yesterday', $byStudent);

        $byClass = $this->home($teacher, ['keyword' => 'Grade 2']);
        $this->assertStringNotContainsString(route('viewAbsen', $first).'"', $byClass);
        $this->assertStringContainsString(route('viewAbsen', $second).'"', $byClass);
        $this->assertStringContainsString(route('viewAbsen', $yesterday).'"', $byClass);

        $nothing = $this->home($teacher, ['keyword' => 'zzz-nobody']);
        $this->assertStringContainsString('No sessions today', $nothing);
        $this->assertStringNotContainsString('Still open from yesterday', $nothing);
    }
}
