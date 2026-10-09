<?php

namespace Tests\Feature\Teacher;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SchedulePageTest extends TeacherTestCase
{
    private User $teacher;
    private int $classId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = $this->newTeacher('Schedule Teacher');
        $this->classId = $this->classFor($this->teacher, 'Grade 1', 1);
        $this->at('2026-10-09 10:00:00');
    }

    private function page(): string
    {
        return $this->asTeacher($this->teacher)->get(route('viewScheduleClassTeacher', $this->classId))->assertOk()->getContent();
    }

    public function test_header_links_include_the_fixed_weekly_url(): void
    {
        $this->sessionAt($this->classId, '2026-10-16 16:00:00');

        $html = $this->page();

        $this->assertStringContainsString('<h1 class="page-title">Schedule · Grade 1</h1>', $html);
        $this->assertStringContainsString('<a href="'.route('viewaddScheduleClass', $this->classId).'" class="btn btn-primary">', $html);
        $this->assertStringContainsString('<a href="'.route('viewaddMultipleScheduleClass', $this->classId).'" class="btn btn-outline-secondary">Add weekly schedules</a>', $html);
        $this->assertStringContainsString('<a href="'.route('viewAllScheduleTeacher', $this->teacher->id).'" class="btn btn-outline-secondary">', $html);
        $this->assertStringNotContainsString(')}}', $html);
    }

    public function test_each_session_shows_its_status_and_only_unrecorded_ones_have_actions(): void
    {
        $recorded = $this->sessionAt($this->classId, '2026-10-02 16:00:00');
        $this->recordRaw($recorded);
        $this->sessionAt($this->classId, '2026-10-08 16:00:00');  // open: inside the window
        $upcoming = $this->sessionAt($this->classId, '2026-10-16 16:00:00');
        $missed = $this->sessionAt($this->classId, '2026-10-01 16:00:00');

        $html = $this->page();

        $row = $this->rowFor($html, '02 Oct 2026');
        $this->assertStringContainsString('<span class="status-badge status-badge-success">Recorded</span>', $row);
        $this->assertStringNotContainsString('Update', $row);
        $this->assertStringNotContainsString('<form', $row);

        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Open</span>', $this->rowFor($html, '08 Oct 2026'));

        // Update is for sessions that have not started; a started one (Open, Missed) is moved by the head. Delete stays for every unrecorded one.
        $row = $this->rowFor($html, '16 Oct 2026');
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Upcoming</span>', $row);
        $this->assertStringContainsString('<a href="'.route('viewUpdateScheduleClassTeacher', ['scheduleId' => $upcoming]).'" class="btn btn-sm btn-outline-secondary">Update</a>', $row);

        $this->assertStringNotContainsString('Update', $this->rowFor($html, '08 Oct 2026'));

        $row = $this->rowFor($html, '01 Oct 2026');
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Missed</span>', $row);
        $this->assertStringNotContainsString('Update', $row);
        $this->assertStringContainsString('action="'.route('deleteScheduleTeacher', ['id' => $missed, 'classId' => $this->classId]).'" data-confirm="Delete the session on 01 Oct 2026, 16:00? This cannot be undone."', $row);
        $this->assertStringContainsString('Delete…', $row);

        // Newest first.
        $this->assertLessThan(strpos($html, '08 Oct 2026'), strpos($html, '16 Oct 2026'));
        $this->assertLessThan(strpos($html, '01 Oct 2026'), strpos($html, '02 Oct 2026'));
    }

    public function test_a_class_without_sessions_shows_the_empty_state(): void
    {
        $html = $this->page();

        $this->assertStringContainsString('No schedules yet', $html);
        $this->assertStringNotContainsString('<table', $html);
    }

    public function test_add_and_weekly_forms_keep_their_field_names(): void
    {
        $add = $this->asTeacher($this->teacher)->get(route('viewaddScheduleClass', $this->classId))->assertOk()->getContent();
        $this->assertStringContainsString('<h1 class="page-title">Add schedule</h1>', $add);
        $this->assertStringContainsString('<p class="page-subtitle">Grade 1</p>', $add);
        $this->assertStringContainsString('action="'.route('addScheduleClassTeacher', ['id' => $this->classId]).'"', $add);
        $this->assertStringContainsString('<input type="hidden" value="'.$this->classId.'" name="classId">', $add);
        $this->assertMatchesRegularExpression('/<input type="datetime-local" id="field-dateTime" name="dateTime" value="" class="form-control" min="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}" required/', $add);
        $this->assertStringContainsString('<a href="'.route('viewScheduleClassTeacher', $this->classId).'" class="btn btn-outline-secondary">', $add);

        $weekly = $this->get(route('viewaddMultipleScheduleClass', $this->classId))->assertOk()->getContent();
        $this->assertStringContainsString('<h1 class="page-title">Add weekly schedules</h1>', $weekly);
        $this->assertStringContainsString('action="'.route('addMultipleScheduleClassTeacher').'"', $weekly);
        $this->assertStringContainsString('<input type="hidden" value="'.$this->classId.'" name="classId">', $weekly);
        $this->assertMatchesRegularExpression('/<input type="datetime-local" id="field-dateTime" name="dateTime" value="" class="form-control" min="\d{4}-\d{2}-\d{2}T\d{2}:\d{2}" required/', $weekly);
        $this->assertStringContainsString('<input type="number" id="field-ScheduleLoop" name="ScheduleLoop" value="" class="form-control" min="1" max="52" required', $weekly);
        $this->assertStringContainsString('Creates one schedule per week, starting from the first session.', $weekly);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_the_date(): void
    {
        $schedule = $this->sessionAt($this->classId, '2026-10-16 16:00:00');
        $html = $this->asTeacher($this->teacher)->get(route('viewUpdateScheduleClassTeacher', ['scheduleId' => $schedule]))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Update schedule</h1>', $html);
        $this->assertStringContainsString('name="dateTime" value="2026-10-16T16:00"', $html);
        $this->assertStringContainsString('<input type="hidden" value="'.$schedule.'" name="scheduleId">', $html);

        $update = route('updateScheduleClassTeacher');
        $this->post($update, $this->formFields($html, $update))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('viewScheduleClassTeacher', $this->classId));

        $this->assertSame('2026-10-16 16:00:00', DB::table('schedules')->where('id', $schedule)->value('date'));
    }
}
