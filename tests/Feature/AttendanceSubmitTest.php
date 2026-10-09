<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AttendanceSubmitTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_status_is_saved_for_the_student_on_the_same_form_row(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        // Form rows in reverse id order: row 0 = highest id student.
        $rows = $students->sortByDesc('id')->values();

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => $rows->pluck('id')->all(),
            'check' => ['on', 'off'] + array_fill(0, $rows->count(), 'on'),
            'keterangan' => ['Select...', 'Sick'] + array_fill(0, $rows->count(), 'Select...'),
            'notes' => array_fill(0, $rows->count(), ''),
            'return_url' => route('teacher'),
        ])->assertRedirect(route('teacher'));

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->first();
        $this->assertSame('Attend', $this->description($header->id, $rows[0]->id));
        $this->assertSame('Sick', $this->description($header->id, $rows[1]->id));
    }

    public function test_attendance_cannot_be_saved_twice(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $payload = [
            'student_id' => $students->pluck('id')->all(),
            'check' => array_fill(0, $students->count(), 'on'),
            'keterangan' => array_fill(0, $students->count(), 'Select...'),
            'notes' => array_fill(0, $students->count(), ''),
            'return_url' => route('teacher'),
        ];
        $quotaBefore = DB::table('students')->where('id', $students[0]->id)->value('Quota');

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), $payload);
        $this->post(route('getAbsen', $schedule->id), $payload);

        $this->assertSame(1, DB::table('header_absens')->where('schedules_id', $schedule->id)->count());
        $this->assertSame($quotaBefore + 1, DB::table('students')->where('id', $students[0]->id)->value('Quota'));
    }

    public function test_students_outside_the_class_are_ignored(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $outsider = DB::table('students')->whereNotIn('id', $students->pluck('id'))->first();

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => [$outsider->id],
            'check' => ['on'],
            'keterangan' => ['Select...'],
            'notes' => [''],
            'return_url' => route('teacher'),
        ]);

        $this->assertDatabaseMissing('detail_absens', ['student_id' => $outsider->id]);
    }

    public function test_head_attendance_keeps_each_status_on_its_own_student(): void
    {
        [, $schedule, $students] = $this->todaysOwnSchedule();
        $rows = $students->sortByDesc('id')->values();

        $this->actingAs(User::where('role', 'head')->firstOrFail())->post(route('head.attendance.update', $schedule->id), [
            'student_id' => $rows->pluck('id')->all(),
            'check' => ['on', 'off'] + array_fill(0, $rows->count(), 'on'),
            'keterangan' => ['Attend', 'Sick'] + array_fill(0, $rows->count(), 'Attend'),
            'notes' => array_fill(0, $rows->count(), ''),
        ])->assertRedirect();

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->first();
        $this->assertSame('Attend', $this->description($header->id, $rows[0]->id));
        $this->assertSame('Sick', $this->description($header->id, $rows[1]->id));
    }

    public function test_form_marks_payment_gated_row_without_shifting_other_rows(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $gated = $students[0];
        $this->makePaymentGated($gated->id, $schedule->id);

        $html = $this->actingAs($teacher)->post(route('viewAbsen', $schedule->id))->assertOk()->getContent();

        $this->assertStringContainsString('Payment required', $html);
        $this->assertStringNotContainsString('value="'.$gated->id.'" name="student_id', $html, 'gated row must not submit its student id');
        $this->assertStringContainsString('name="keterangan[1]"', $html, 'fields carry the row index');
        $this->assertStringNotContainsString('name="keterangan[]"', $html);
    }

    public function test_browser_payload_with_a_gated_row_keeps_statuses_on_the_right_students(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $gated = $students[0];
        $other = $students[1];
        $this->makePaymentGated($gated->id, $schedule->id);
        $quotaBefore = DB::table('students')->where('id', $gated->id)->value('Quota');

        // What the browser sends: row 0 is gated (no fields), row 1 is "Sick".
        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => [1 => $other->id],
            'check' => [1 => 'off'],
            'keterangan' => [1 => 'Sick'],
            'notes' => [1 => ''],
            'return_url' => route('teacher'),
        ]);

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertSame('Sick', $this->description($header, $other->id));
        $this->assertNull($this->description($header, $gated->id));
        $this->assertSame($quotaBefore, DB::table('students')->where('id', $gated->id)->value('Quota'));
    }

    public function test_server_refuses_attendance_for_a_payment_gated_student_even_if_posted(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $gated = $students[0];
        $this->makePaymentGated($gated->id, $schedule->id);

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => [0 => $gated->id],
            'check' => [0 => 'on'],
            'keterangan' => [0 => 'Select...'],
            'notes' => [0 => ''],
            'return_url' => route('teacher'),
        ]);

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertNull($this->description($header, $gated->id));
    }

    public function test_head_permission_note_is_saved(): void
    {
        [, $schedule, $students] = $this->todaysOwnSchedule();

        $this->actingAs(User::where('role', 'head')->firstOrFail())->post(route('head.attendance.update', $schedule->id), [
            'student_id' => [0 => $students[0]->id],
            'check' => [0 => 'off'],
            'keterangan' => [0 => 'Permission'],
            'notes' => [0 => 'Acara keluarga'],
        ]);

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertSame('Acara keluarga', DB::table('detail_absens')->where('header_absen_id', $header)->where('student_id', $students[0]->id)->value('Notes'));
    }

    public function test_teacher_records_a_class_member_with_null_nis(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $member = $students[0];
        DB::table('students')->where('id', $member->id)->update(['nis' => null]);

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => [0 => $member->id],
            'check' => [0 => 'on'],
            'keterangan' => [0 => 'Select...'],
            'notes' => [0 => ''],
            'return_url' => route('teacher'),
        ])->assertSessionHas('msg', 'Success Making Attendance');

        $header = DB::table('header_absens')->where('schedules_id', $schedule->id)->value('id');
        $this->assertSame('Attend', $this->description($header, $member->id));
    }

    public function test_teacher_flash_counts_skipped_rows(): void
    {
        [$teacher, $schedule, $students] = $this->todaysOwnSchedule();
        $outsider = DB::table('students')->whereNotIn('id', $students->pluck('id'))->first();

        $this->actingAs($teacher)->post(route('getAbsen', $schedule->id), [
            'student_id' => [$students[0]->id, $outsider->id],
            'check' => ['on', 'on'],
            'keterangan' => ['Select...', 'Select...'],
            'notes' => ['', ''],
            'return_url' => route('teacher'),
        ])->assertSessionHas('msg', 'Success Making Attendance (1 row skipped)');
    }

    /** Quota used up + oldest unpaid bill 25 days before the class: the attendance page asks for payment. */
    private function makePaymentGated(int $studentId, int $scheduleId): void
    {
        $date = \Illuminate\Support\Carbon::parse(DB::table('schedules')->where('id', $scheduleId)->value('date'));
        DB::table('students')->where('id', $studentId)->update(['Quota' => 3]);
        DB::table('transactions')->where('students_id', $studentId)->where('payment_status', 'Unpaid')->delete();
        DB::table('transactions')->insert([
            'students_id' => $studentId, 'transaction_date' => $date->copy()->subDays(25)->toDateString(),
            'payment_status' => 'Unpaid', 'discount' => 0, 'price' => 400000, 'desc' => '-',
        ]);
    }

    /** @return array{0: User, 1: object, 2: \Illuminate\Support\Collection} */
    private function todaysOwnSchedule(): array
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $schedule = DB::table('schedules as s')
            ->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->where('m.user_id', $teacher->id)
            ->whereDate('s.date', now('Asia/Jakarta')->toDateString())
            ->select('s.id', 's.class_id')
            ->firstOrFail();
        $students = DB::table('students as st')
            ->join('mapping_class_children as c', 'c.student_id', 'st.id')
            ->where('c.class_id', $schedule->class_id)
            ->orderBy('st.id')
            ->get(['st.id', 'st.nis']);

        return [$teacher, $schedule, $students];
    }

    private function description(int $headerId, int $studentId): ?string
    {
        return DB::table('detail_absens')->where('header_absen_id', $headerId)->where('student_id', $studentId)->value('Description');
    }
}
