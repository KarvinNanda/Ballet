<?php

namespace Tests\Feature\Staff;

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;

class HeadAttendanceTest extends StaffTestCase
{
    /** @return array{0: Schedule, 1: \Illuminate\Support\Collection} a schedule with no attendance yet and its active students */
    private function freshSchedule(): array
    {
        $schedule = Schedule::whereNotIn('id', DB::table('header_absens')->pluck('schedules_id'))
            ->whereIn('class_id', DB::table('mapping_class_children')->pluck('class_id'))
            ->firstOrFail();
        $students = DB::table('mapping_class_children')->join('students', 'students.id', 'mapping_class_children.student_id')
            ->where('mapping_class_children.class_id', $schedule->class_id)->where('students.Status', 'aktif')
            ->select('students.*')->get();

        return [$schedule, $students];
    }

    private function payload($students, string $check = 'on', string $note = 'Select...'): array
    {
        return [
            'student_id' => $students->pluck('id')->all(),
            'check' => array_fill(0, $students->count(), $check),
            'keterangan' => array_fill(0, $students->count(), $note),
            'notes' => array_fill(0, $students->count(), ''),
        ];
    }

    public function test_admin_cannot_record_attendance(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $this->asRole('admin')->post(route('admin.attendance.update', $schedule), $this->payload($students))->assertForbidden();
        $this->assertFalse(DB::table('header_absens')->where('schedules_id', $schedule->id)->exists());
    }

    public function test_first_record_adds_one_quota_and_an_edit_adds_none(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $id = $students->first()->id;
        $before = DB::table('students')->where('id', $id)->value('Quota');

        $this->asRole('head')->post(route('head.attendance.update', $schedule), $this->payload($students))->assertRedirect();
        $this->post(route('head.attendance.update', $schedule), $this->payload($students, 'off', 'Sick'))->assertRedirect();

        $this->assertSame($before + 1, DB::table('students')->where('id', $id)->value('Quota'));
        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertSame('Sick', DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $id)->value('Description'));
    }

    public function test_head_edit_page_shows_each_students_own_record(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $this->assertGreaterThanOrEqual(2, $students->count(), 'need two students');
        $rows = $students->sortByDesc('id')->values();
        $payload = $this->payload($rows);
        $payload['check'][1] = 'off';
        $payload['keterangan'][1] = 'Sick';
        $this->asRole('head')->post(route('head.attendance.update', $schedule), $payload);

        // The page must mark row of $rows[1] (by NIS) as Sick, whatever order it lists students in.
        $html = $this->get(route('head.attendance.edit', $schedule))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/'.preg_quote($rows[1]->nis, '/').'.*?Sick/s', $html);
        // Stronger: within that student's own row (before the next student_id[] field), Sick is the selected option.
        $own = '/value="'.$rows[1]->id.'" name="student_id\\[\\d+\\]">(?:(?!name="student_id\\[).)*?<option value="Sick" selected>/s';
        $this->assertMatchesRegularExpression($own, $html);
        $first = '/value="'.$rows[0]->id.'" name="student_id\\[\\d+\\]">(?:(?!name="student_id\\[).)*?value="on" checked>/s';
        $this->assertMatchesRegularExpression($first, $html);
    }

    public function test_head_cannot_record_a_student_who_must_pay_first(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $student = $students->first();
        // Force the gate: Quota at 3, unpaid bill 25 days from the class date, non-Intensive class.
        DB::table('students')->where('id', $student->id)->update(['Quota' => 3]);
        DB::table('transactions')->where('students_id', $student->id)->delete();
        \App\Models\Transaction::factory()->create([
            'students_id' => $student->id, 'payment_status' => 'Unpaid',
            'transaction_date' => \Illuminate\Support\Carbon::parse($schedule->date)->addDays(25)->toDateString(),
        ]);
        $className = (string) DB::table('class_transactions as ct')->join('class_types as t', 't.id', 'ct.class_type_id')->where('ct.id', $schedule->class_id)->value('t.class_name');
        if (str_contains($className, 'Intensive') || str_contains($className, 'Pointe')) {
            $this->markTestSkipped('first fresh schedule is an Intensive/Pointe class');
        }

        $this->asRole('head')->post(route('head.attendance.update', $schedule), $this->payload(collect([$student])));
        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertFalse(DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $student->id)->exists());
        $this->assertSame(3, DB::table('students')->where('id', $student->id)->value('Quota'));
    }

    public function test_head_records_a_class_member_with_null_nis(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $member = $students->first();
        DB::table('students')->where('id', $member->id)->update(['nis' => null]);
        $before = DB::table('students')->where('id', $member->id)->value('Quota');

        $this->asRole('head')->post(route('head.attendance.update', $schedule), $this->payload(collect([$member])))
            ->assertSessionHas('msg', 'Success Update Attendance');

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertSame('Attend', DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $member->id)->value('Description'));
        $this->assertSame($before + 1, DB::table('students')->where('id', $member->id)->value('Quota'));
    }

    public function test_head_ignores_outsiders_and_says_how_many_rows_were_skipped(): void
    {
        [$schedule, $students] = $this->freshSchedule();
        $outsiders = DB::table('students')->whereNotIn('id', DB::table('mapping_class_children')->where('class_id', $schedule->class_id)->pluck('student_id'))
            ->limit(2)->get();
        $this->assertCount(2, $outsiders);

        $this->asRole('head')->post(route('head.attendance.update', $schedule), $this->payload($students->take(1)->concat($outsiders)->values()))
            ->assertSessionHas('msg', 'Success Update Attendance (2 rows skipped)');

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertFalse(DB::table('detail_absens')->where('header_absen_id', $header)->whereIn('student_id', $outsiders->pluck('id'))->exists());
        $this->assertTrue(DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $students->first()->id)->exists());
    }

    public function test_unknown_description_is_a_validation_error(): void
    {
        $schedule = \App\Models\Schedule::firstOrFail();
        $this->asRole('head')->post(route('head.attendance.update', $schedule), [
            'student_id' => [1], 'check' => ['off'], 'keterangan' => ['Holiday'], 'notes' => [''],
        ])->assertSessionHasErrors('keterangan.0');
    }
}
