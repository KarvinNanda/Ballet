<?php

namespace Tests\Feature\Teacher;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AttendancePageTest extends TeacherTestCase
{
    private User $teacher;
    private int $classId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = $this->newTeacher('Attendance Teacher');
        $this->classId = $this->classFor($this->teacher, 'Grade 1', 3);
        $this->at('2026-10-09 10:00:00');
    }

    /** Opens the attendance page from the home page, as the "Take attendance" button does. */
    private function open(int $schedule): string
    {
        return $this->asTeacher($this->teacher)->from(route('teacher'))->post(route('viewAbsen', $schedule))->assertOk()->getContent();
    }

    /** The rendered form as the browser would post it; parse_str turns "check[0]" keys into arrays. */
    private function renderedPayload(string $html, int $schedule): array
    {
        parse_str(http_build_query($this->formFields($html, route('getAbsen', $schedule))), $payload);

        return $payload;
    }

    private function description(int $schedule, int $studentId): ?string
    {
        $header = DB::table('header_absens')->where('schedules_id', $schedule)->value('id');

        return DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $studentId)->value('Description');
    }

    public function test_the_open_form_keeps_the_field_names_and_asks_before_saving(): void
    {
        $schedule = $this->sessionAt($this->classId, '2026-10-09 09:00:00');
        $first = $this->studentsOf($this->classId)->first();

        $html = $this->open($schedule);

        $title = 'Attendance · '.$this->expectedClassLabel($this->classId).' · Fri 09 Oct 2026, 09:00';
        $this->assertStringContainsString('<h1 class="page-title">'.e($title).'</h1>', $html);
        $this->assertStringContainsString('<a href="'.route('teacher').'" class="btn btn-outline-secondary">', $html);
        $this->assertStringContainsString('action="'.route('getAbsen', $schedule).'" data-confirm="Attendance cannot be changed after saving. Save now?" class="card"', $html);
        $this->assertStringContainsString('<input type="hidden" name="return_url" value="'.route('teacher').'">', $html);
        $this->assertStringContainsString('<input type="hidden" value="'.$first->id.'" name="student_id[0]">', $html);
        $this->assertStringContainsString('<input type="hidden" name="check[0]" value="off">', $html);
        $this->assertStringContainsString('<input type="checkbox" id="present-0" name="check[0]" class="form-check-input" value="on" checked>', $html);
        $this->assertStringContainsString('<select id="reason-0" name="keterangan[0]" class="form-select">', $html);
        $this->assertStringContainsString('<option value="" selected>No reason</option>', $html);
        $this->assertStringContainsString('name="notes[0]" class="form-control" maxlength="255" value=""', $html);
        $this->assertStringContainsString('Save attendance', $html);
        $this->assertStringNotContainsString('Recorded. Ask the head to correct it.', $html);
    }

    public function test_posting_the_rendered_form_unchanged_records_everyone_present(): void
    {
        $schedule = $this->sessionAt($this->classId, '2026-10-09 09:00:00');
        $html = $this->open($schedule);

        $this->post(route('getAbsen', $schedule), $this->renderedPayload($html, $schedule))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('msg', 'Success Making Attendance');

        foreach ($this->studentsOf($this->classId) as $student) {
            $this->assertSame('Attend', $this->description($schedule, $student->id), $student->LongName);
        }
    }

    public function test_posting_the_rendered_form_with_a_gated_row_keeps_each_status_on_its_student(): void
    {
        [$gated, $present, $sick] = $this->studentsOf($this->classId)->all();
        $schedule = $this->sessionAt($this->classId, '2026-10-09 09:00:00');
        // Payment gate: quota used up and the oldest unpaid bill 25 days before the class (Grade 1 is not Intensive/Pointe).
        DB::table('students')->where('id', $gated->id)->update(['Quota' => 3]);
        DB::table('transactions')->insert([
            'students_id' => $gated->id, 'transaction_date' => '2026-09-14',
            'payment_status' => 'Unpaid', 'discount' => 0, 'price' => 400000, 'desc' => '-',
        ]);

        $html = $this->open($schedule);
        $gatedRow = $this->rowFor($html, $gated->LongName);
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Payment required</span>', $gatedRow);
        $this->assertStringNotContainsString('name="student_id', $gatedRow);

        $payload = $this->renderedPayload($html, $schedule);
        $this->assertSame([1 => (string) $present->id, 2 => (string) $sick->id], $payload['student_id']);
        $payload['check'][2] = 'off'; // an unticked box sends only its hidden "off"
        $payload['keterangan'][2] = 'Sick';
        $this->post(route('getAbsen', $schedule), $payload)
            ->assertSessionHasNoErrors()
            ->assertSessionHas('msg', 'Success Making Attendance');

        $this->assertSame('Attend', $this->description($schedule, $present->id));
        $this->assertSame('Sick', $this->description($schedule, $sick->id));
        $this->assertNull($this->description($schedule, $gated->id));
        $this->assertSame(3, (int) DB::table('students')->where('id', $gated->id)->value('Quota'));
    }

    public function test_a_recorded_session_is_read_only_and_shows_what_was_stored(): void
    {
        $classId = $this->classFor($this->teacher, 'Grade 1', 0);
        $stored = [
            'Ayu Attend' => ['Attend', '-'], 'Sinta Sakit' => ['Sakit', 'Demam'], 'Indah Izin' => ['Izin', 'Acara keluarga'],
            'Bela Absent' => ['Absent', ''], 'Siska Sick' => ['Sick', ''], 'Lina Legacy' => ['Alpha', ''],
        ];
        $rows = [];
        foreach ($stored as $name => $row) {
            $student = Student::factory()->create(['LongName' => $name]);
            $this->enrol($classId, $student);
            $rows[$student->id] = $row;
        }
        $schedule = $this->sessionAt($classId, '2026-10-02 16:00:00');
        $this->recordRaw($schedule, $rows);

        $html = $this->open($schedule); // a week later: outside the window, still viewable

        $this->assertStringContainsString('<div class="alert alert-info" role="status">Recorded. Ask the head to correct it.</div>', $html);
        $this->assertStringContainsString('<span class="status-badge status-badge-success">Present</span>', $this->rowFor($html, 'Ayu Attend'));
        $sakit = $this->rowFor($html, 'Sinta Sakit');
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Sick</span>', $sakit);
        $this->assertStringContainsString('<td>Demam</td>', $sakit);
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Permission</span>', $this->rowFor($html, 'Indah Izin'));
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Absent</span>', $this->rowFor($html, 'Bela Absent'));
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Sick</span>', $this->rowFor($html, 'Siska Sick'));
        $this->assertMatchesRegularExpression('/<td>\s*Alpha\s*<\/td>/', $this->rowFor($html, 'Lina Legacy'));
        $this->assertStringNotContainsString(route('getAbsen', $schedule), $html);
        $this->assertStringNotContainsString('name="check', $html);
    }

    public function test_a_second_save_is_still_refused(): void
    {
        $schedule = $this->sessionAt($this->classId, '2026-10-09 09:00:00');
        $first = $this->studentsOf($this->classId)->first();
        $this->recordRaw($schedule, [$first->id => ['Attend', '-']]);
        $quotas = $this->studentsOf($this->classId)->pluck('Quota', 'id')->all();

        $this->asTeacher($this->teacher)->post(route('getAbsen', $schedule), $this->everyonePresent($this->classId, route('teacher')))
            ->assertRedirect(route('teacher'))
            ->assertSessionHas('error');

        $header = DB::table('header_absens')->where('schedules_id', $schedule)->value('id');
        $this->assertSame(1, DB::table('header_absens')->where('schedules_id', $schedule)->count());
        $this->assertSame(1, DB::table('detail_absens')->where('header_absen_id', $header)->count());
        $this->assertSame($quotas, $this->studentsOf($this->classId)->pluck('Quota', 'id')->all());
    }

    public function test_a_class_without_active_students_shows_the_empty_state_and_no_save_button(): void
    {
        $empty = $this->classFor($this->teacher, 'Grade 1', 0);
        $schedule = $this->sessionAt($empty, '2026-10-09 09:00:00');

        $html = $this->open($schedule);

        $this->assertStringContainsString('No active students in this class', $html);
        $this->assertStringNotContainsString('Save attendance', $html);
    }
}
