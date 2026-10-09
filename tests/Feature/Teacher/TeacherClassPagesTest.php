<?php

namespace Tests\Feature\Teacher;

use App\Models\Student;
use App\Models\User;

class TeacherClassPagesTest extends TeacherTestCase
{
    /** @return array{0: User, 1: int, 2: int} a teacher with Grade 1 (2 active students + 1 inactive) and Grade 2 (no students) */
    private function teacherWithClasses(): array
    {
        $teacher = $this->newTeacher('List Teacher');
        $grade1 = $this->classFor($teacher, 'Grade 1', 2);
        $this->enrol($grade1, Student::factory()->create(['Status' => 'non-aktif']));
        $grade2 = $this->classFor($teacher, 'Grade 2', 0);
        $this->classFor($teacher, 'Grade 3', 1, 'non-aktif');
        $this->classFor($this->newTeacher('Someone Else'), 'Grade 4', 1);

        return [$teacher, $grade1, $grade2];
    }

    private function assertClassList(string $html, int $grade1, int $grade2): void
    {
        $row = $this->rowFor($html, 'Grade 1');
        $this->assertStringContainsString('<td>2</td>', $row);
        $this->assertStringContainsString('action="'.route('viewDetailTeacher', $grade1).'"', $row);
        $this->assertStringContainsString('href="'.route('viewScheduleClassTeacher', $grade1).'"', $row);

        $row = $this->rowFor($html, 'Grade 2');
        $this->assertStringContainsString('<td>0</td>', $row);
        $this->assertStringContainsString('href="'.route('viewScheduleClassTeacher', $grade2).'"', $row);

        $this->assertStringNotContainsString('Grade 3', $html, 'inactive class');
        $this->assertStringNotContainsString('Grade 4', $html, "another teacher's class");
    }

    public function test_my_classes_lists_active_classes_with_active_student_counts(): void
    {
        [$teacher, $grade1, $grade2] = $this->teacherWithClasses();

        $html = $this->asTeacher($teacher)->get(route('viewClass'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">My classes</h1>', $html);
        $this->assertStringNotContainsString('Today Schedule', $html);
        $this->assertClassList($html, $grade1, $grade2);
    }

    public function test_schedules_page_shows_the_same_list(): void
    {
        [$teacher, $grade1, $grade2] = $this->teacherWithClasses();

        $html = $this->asTeacher($teacher)->get(route('viewAllScheduleTeacher', $teacher->id))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Schedules</h1>', $html);
        $this->assertClassList($html, $grade1, $grade2);
    }

    public function test_a_teacher_without_classes_sees_the_empty_state_on_both_lists(): void
    {
        $teacher = $this->newTeacher('No Class Teacher');

        foreach ([route('viewClass'), route('viewAllScheduleTeacher', $teacher->id)] as $url) {
            $this->asTeacher($teacher)->get($url)->assertOk()
                ->assertSeeText('No classes yet')
                ->assertDontSee('<table', false);
        }
    }

    public function test_students_page_shows_name_age_and_quota_with_the_staff_max_rule(): void
    {
        $this->at('2026-10-09 10:00:00');
        $teacher = $this->newTeacher();
        $classId = $this->classFor($teacher, 'Grade 1', 0);
        $this->enrol($classId, Student::factory()->create(['LongName' => 'Rani Regular', 'Dob' => '2016-05-01', 'Quota' => 1]));
        $this->enrol($classId, Student::factory()->create(['LongName' => 'Tia Trial', 'Status' => 'trial', 'Quota' => 0]));
        $this->enrol($classId, Student::factory()->create(['LongName' => 'Kara Custom', 'Quota' => 2]), classQuota: 8);
        $this->enrol($classId, Student::factory()->create(['LongName' => 'Nina Gone', 'Status' => 'non-aktif']));

        $html = $this->asTeacher($teacher)->post(route('viewDetailTeacher', $classId))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Students · Grade 1</h1>', $html);
        $this->assertStringContainsString('<a href="'.route('viewClass').'" class="btn btn-outline-secondary">', $html);
        $rani = $this->rowFor($html, 'Rani Regular');
        $this->assertMatchesRegularExpression('/<td>\s*10\s*<\/td>/', $rani);
        $this->assertStringContainsString('<td>1 / 3</td>', $rani);
        $this->assertStringContainsString('<td>0 / 2</td>', $this->rowFor($html, 'Tia Trial'));
        $this->assertStringContainsString('<td>2 / 8</td>', $this->rowFor($html, 'Kara Custom'));
        $this->assertStringNotContainsString('Nina Gone', $html);
    }

    public function test_course_defaults_apply_when_the_class_quota_is_not_set(): void
    {
        $teacher = $this->newTeacher();

        foreach (['Pointe Class' => 4, 'Intensive Kids' => 12, 'Intensive Class' => 12, 'Grade 5' => 3] as $course => $max) {
            $classId = $this->classFor($teacher, $course, 0);
            $this->enrol($classId, Student::factory()->create(['LongName' => "Kid of {$course}", 'Quota' => 0]));

            $html = $this->asTeacher($teacher)->post(route('viewDetailTeacher', $classId))->assertOk()->getContent();
            $this->assertStringContainsString('<td>0 / '.$max.'</td>', $this->rowFor($html, "Kid of {$course}"), $course);
        }
    }

    public function test_students_page_without_students_shows_the_empty_state(): void
    {
        $teacher = $this->newTeacher();
        $classId = $this->classFor($teacher, 'Grade 1', 0);

        $this->asTeacher($teacher)->post(route('viewDetailTeacher', $classId))->assertOk()
            ->assertSeeText('No students in this class yet')
            ->assertDontSee('<table', false);
    }
}
