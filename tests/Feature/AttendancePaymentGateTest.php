<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * teacher/absen.blade.php blocks attendance for a student whose oldest unpaid bill is 20-39 days
 * away from the class date (once quota is used up). Carbon 3 made diffInDays() signed and fractional,
 * which silently broke this gate for bills dated before the class.
 */
class AttendancePaymentGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_with_bill_25_days_before_class_must_pay_first(): void
    {
        [$teacher, $schedule, $studentId] = $this->todaysScheduleWithStudent();
        $this->giveUnpaidBill($studentId, Carbon::parse($schedule->date)->subDays(25));

        $this->actingAs($teacher)->post(route('viewAbsen', $schedule->id))
            ->assertOk()
            ->assertSee('Payment required');
    }

    public function test_student_with_bill_10_days_before_class_can_attend(): void
    {
        [$teacher, $schedule, $studentId] = $this->todaysScheduleWithStudent();
        $this->giveUnpaidBill($studentId, Carbon::parse($schedule->date)->subDays(10));

        $this->actingAs($teacher)->post(route('viewAbsen', $schedule->id))
            ->assertOk()
            ->assertDontSee('Payment required');
    }

    /** @return array{0: User, 1: object, 2: int} */
    private function todaysScheduleWithStudent(): array
    {
        $teacher = User::where('email', 'teacher@gmail.com')->firstOrFail();
        $schedule = DB::table('schedules as s')
            ->join('mapping_class_teachers as m', 'm.class_id', 's.class_id')
            ->join('class_transactions as ct', 'ct.id', 's.class_id')
            ->join('class_types as t', 't.id', 'ct.class_type_id')
            ->where('m.user_id', $teacher->id)
            ->whereDate('s.date', now('Asia/Jakarta')->toDateString())
            ->where('t.class_name', 'not like', '%Intensive%')
            ->where('t.class_name', 'not like', '%Pointe%')
            ->select('s.id', 's.class_id', 's.date')
            ->firstOrFail();

        $studentId = DB::table('mapping_class_children')->where('class_id', $schedule->class_id)->value('student_id');

        // Gate applies only when the quota is used up, and to the oldest unpaid bill.
        DB::table('students')->where('id', $studentId)->update(['Quota' => 3]);
        DB::table('transactions')->where('students_id', $studentId)->where('payment_status', 'Unpaid')->delete();

        return [$teacher, $schedule, $studentId];
    }

    private function giveUnpaidBill(int $studentId, Carbon $date): void
    {
        DB::table('transactions')->insert([
            'students_id' => $studentId,
            'class_transactions_id' => null,
            'transaction_date' => $date->toDateString(),
            'payment_status' => 'Unpaid',
            'discount' => 0,
            'price' => 400000,
            'desc' => '-',
        ]);
    }
}
