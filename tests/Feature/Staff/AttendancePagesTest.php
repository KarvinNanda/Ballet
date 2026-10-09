<?php

namespace Tests\Feature\Staff;

use App\Models\Schedule;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AttendancePagesTest extends StaffTestCase
{
    /** @return array{0: Schedule, 1: \Illuminate\Support\Collection} a schedule with no attendance yet and its active students */
    private function freshSchedule(): array
    {
        $schedule = Schedule::whereNotIn('id', DB::table('header_absens')->pluck('schedules_id'))
            ->whereIn('class_id', DB::table('mapping_class_children')->pluck('class_id'))
            ->firstOrFail();
        $students = DB::table('mapping_class_children')->join('students', 'students.id', 'mapping_class_children.student_id')
            ->where('mapping_class_children.class_id', $schedule->class_id)->where('students.Status', 'aktif')
            ->select('students.*')->orderBy('students.id')->get();

        return [$schedule, $students];
    }

    /** @param array<int, array{0: string, 1: string, 2: string}> $rows student id => [check, keterangan, notes] */
    private function record(Schedule $schedule, array $rows): void
    {
        $this->post(route('head.attendance.update', $schedule), [
            'student_id' => array_keys($rows),
            'check' => array_column($rows, 0),
            'keterangan' => array_column($rows, 1),
            'notes' => array_column($rows, 2),
        ])->assertSessionHasNoErrors()->assertRedirect();
    }

    /** @return list<array<string, mixed>> the detail rows of the schedule, by student */
    private function details(Schedule $schedule): array
    {
        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');

        return DB::table('detail_absens')->where('header_absen_id', $header)->orderBy('student_id')
            ->get(['student_id', 'Description', 'Notes'])->map(fn ($r) => (array) $r)->all();
    }

    /** The rendered form as the browser would post it; parse_str turns "check[0]" keys into arrays. */
    private function renderedPayload(Schedule $schedule): array
    {
        $html = $this->get(route('head.attendance.edit', $schedule))->assertOk()->getContent();
        parse_str(http_build_query($this->formFields($html, route('head.attendance.update', $schedule))), $payload);

        return $payload;
    }

    public function test_header_names_the_class_and_the_session(): void
    {
        [$schedule] = $this->freshSchedule();
        $title = 'Attendance · '.$this->expectedClassLabel($schedule->class_id).' · '.Carbon::parse($schedule->date)->format('D d M Y, H:i');

        $this->asRole('head')->get(route('head.attendance.edit', $schedule))->assertOk()
            ->assertSee('<h1 class="page-title">'.e($title).'</h1>', false)
            ->assertSee('href="'.route('head.schedule.index', $schedule->class_id).'"', false)
            ->assertSeeText('Reason if absent')
            ->assertSee('<option value="" selected>No reason</option>', false)
            ->assertSeeText('Save attendance');
    }

    public function test_empty_reason_with_present_unticked_is_recorded_as_absent(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $id = $students->first()->id;

        $this->asRole('head');
        $this->record($schedule, [$id => ['off', '', '']]);

        $this->assertSame('Absent', collect($this->details($schedule))->firstWhere('student_id', $id)['Description']);
    }

    public function test_posting_the_rendered_form_unchanged_keeps_every_record(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $this->assertGreaterThanOrEqual(2, $students->count(), 'need two students');
        $values = [['on', '', ''], ['off', 'Sick', ''], ['off', 'Permission', 'Doctor visit'], ['off', '', '']];
        $rows = [];
        foreach ($students->values() as $n => $student) {
            $rows[$student->id] = $values[$n % 4];
        }

        $this->asRole('head');
        $this->record($schedule, $rows);
        $before = $this->details($schedule);
        $quota = DB::table('students')->whereIn('id', $students->pluck('id'))->orderBy('id')->pluck('Quota', 'id')->all();

        $this->post(route('head.attendance.update', $schedule), $this->renderedPayload($schedule))
            ->assertSessionHasNoErrors()->assertSessionHas('msg', 'Success Update Attendance');

        $this->assertSame($before, $this->details($schedule));
        $this->assertSame($quota, DB::table('students')->whereIn('id', $students->pluck('id'))->orderBy('id')->pluck('Quota', 'id')->all());
    }

    public function test_unticking_present_on_an_attended_row_records_absent(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $id = $students->first()->id;

        $this->asRole('head');
        $this->record($schedule, [$id => ['on', '', '']]);

        $payload = $this->renderedPayload($schedule);
        $index = array_search((string) $id, $payload['student_id'], true);
        $this->assertNotFalse($index);
        $payload['check'][$index] = 'off'; // an unticked box sends only its hidden "off"
        $this->post(route('head.attendance.update', $schedule), $payload)->assertSessionHasNoErrors();

        $this->assertSame('Absent', collect($this->details($schedule))->firstWhere('student_id', $id)['Description']);
    }

    public function test_indonesian_legacy_descriptions_preselect_their_english_reason(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $this->assertGreaterThanOrEqual(2, $students->count(), 'the fresh schedule needs two active students');
        [$sick, $permission] = [$students->get(0), $students->get(1)];
        // If either student is payment-gated on this schedule, their row has no select: pick two students without
        // the "Payment required" badge instead (report it), do not weaken the assertions.
        $this->asRole('head');
        $this->record($schedule, [$sick->id => ['off', 'Sick', ''], $permission->id => ['off', 'Permission', '']]);
        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $sick->id)->update(['Description' => 'Sakit']);
        DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $permission->id)->update(['Description' => 'Izin']);

        $html = $this->get(route('head.attendance.edit', $schedule))->getContent();

        $this->assertStringContainsString('<option value="Sick" selected>Sick</option>', $this->rowFor($html, $sick->LongName));
        $this->assertStringContainsString('<option value="Permission" selected>Permission</option>', $this->rowFor($html, $permission->LongName));
        $this->assertStringNotContainsString('Saved as', $this->rowFor($html, $sick->LongName));
    }

    public function test_an_unknown_legacy_description_is_shown_and_not_preselected(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $student = $students->first();
        $this->asRole('head');
        $this->record($schedule, [$student->id => ['off', 'Sick', '']]);
        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $student->id)->update(['Description' => 'Alpha']);

        $row = $this->rowFor($this->get(route('head.attendance.edit', $schedule))->getContent(), $student->LongName);

        $this->assertStringContainsString('Saved as “Alpha”', $row);
        $this->assertStringContainsString('<option value="" selected>No reason</option>', $row);
    }

    public function test_payment_required_row_has_no_inputs(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $student = $students->first();
        // Force the gate: Quota at 3, unpaid bill 25 days from the class date, non-Intensive class (as HeadAttendanceTest).
        DB::table('students')->where('id', $student->id)->update(['Quota' => 3]);
        DB::table('transactions')->where('students_id', $student->id)->delete();
        \App\Models\Transaction::factory()->create([
            'students_id' => $student->id, 'payment_status' => 'Unpaid',
            'transaction_date' => Carbon::parse($schedule->date)->addDays(25)->toDateString(),
        ]);
        $className = (string) DB::table('class_transactions as ct')->join('class_types as t', 't.id', 'ct.class_type_id')->where('ct.id', $schedule->class_id)->value('t.class_name');
        if (str_contains($className, 'Intensive') || str_contains($className, 'Pointe')) {
            $this->markTestSkipped('first fresh schedule is an Intensive/Pointe class');
        }

        $row = $this->rowFor($this->asRole('head')->get(route('head.attendance.edit', $schedule))->assertOk()->getContent(), $student->LongName);

        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Payment required</span>', $row);
        $this->assertStringNotContainsString('name="student_id', $row);
        $this->assertStringNotContainsString('name="keterangan', $row);
    }

    /** @param array<int, array{0: ?string, 1: ?string}> $rows student id => [Description, Notes]: raw rows as legacy/seeded data has them, not written by the recorder */
    private function seedRawRows(Schedule $schedule, array $rows): void
    {
        $header = DB::table('header_absens')->insertGetId(['schedules_id' => $schedule->id, 'teacher_id' => null]);
        foreach ($rows as $studentId => [$description, $notes]) {
            DB::table('detail_absens')->insert(['header_absen_id' => $header, 'student_id' => $studentId, 'Description' => $description, 'Notes' => $notes]);
        }
    }

    public function test_posting_the_rendered_form_unchanged_keeps_legacy_notes(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $this->assertGreaterThanOrEqual(3, $students->count(), 'need three students');
        [$sakit, $izin, $attend] = [$students->get(0)->id, $students->get(1)->id, $students->get(2)->id];
        $this->seedRawRows($schedule, [$sakit => ['Sakit', 'Demam'], $izin => ['Izin', 'Acara keluarga'], $attend => ['Attend', '-']]);
        $quota = DB::table('students')->whereIn('id', [$sakit, $izin, $attend])->orderBy('id')->pluck('Quota', 'id')->all();

        $this->asRole('head');
        $this->post(route('head.attendance.update', $schedule), $this->renderedPayload($schedule))->assertSessionHasNoErrors();

        $details = collect($this->details($schedule))->keyBy('student_id');
        $this->assertSame(['Sick', 'Demam'], [$details[$sakit]['Description'], $details[$sakit]['Notes']]);
        $this->assertSame(['Permission', 'Acara keluarga'], [$details[$izin]['Description'], $details[$izin]['Notes']]);
        $this->assertSame(['Attend', '-'], [$details[$attend]['Description'], $details[$attend]['Notes']]);
        $this->assertSame($quota, DB::table('students')->whereIn('id', [$sakit, $izin, $attend])->orderBy('id')->pluck('Quota', 'id')->all());
    }

    public function test_an_empty_description_does_not_render_saved_as(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $student = $students->first();
        $this->seedRawRows($schedule, [$student->id => [null, null]]);

        $row = $this->rowFor($this->asRole('head')->get(route('head.attendance.edit', $schedule))->assertOk()->getContent(), $student->LongName);

        $this->assertStringNotContainsString('Saved as', $row);
        $this->assertStringContainsString('<option value="" selected>No reason</option>', $row);
    }
}
