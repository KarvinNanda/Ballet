<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ReportTest extends StaffTestCase
{
    public function test_admin_now_has_stock_and_teacher_reports(): void
    {
        $this->asRole('admin')->get(route('admin.report.stock'))->assertOk();
        $this->asRole('admin')->get(route('admin.report.teacher'))->assertOk();
    }

    public function test_class_report_print_for_a_class_without_schedules_does_not_500(): void
    {
        $classId = DB::table('class_transactions')->whereNotIn('id', DB::table('schedules')->pluck('class_id'))->value('id')
            ?? \App\Models\ClassTransaction::factory()->create()->id;

        $this->asRole('head')->post(route('head.report.class.print', ['header' => $classId, 'teacher' => 'Nobody']))
            ->assertStatus(200);
    }

    public function test_active_student_print_works_for_both_roles(): void
    {
        foreach (['admin', 'head'] as $role) {
            $this->asRole($role)->post(route("{$role}.report.active-student.print"), [])
                ->assertOk()
                ->assertHeader('content-type', 'application/pdf');
        }
    }

    /** Teacher report of $month counts fees billed in $month or the month after; December must reach into January. */
    public function test_teacher_report_for_december_includes_december_and_january_fees(): void
    {
        $year = now('Asia/Jakarta')->year;
        $header = DB::table('header_absens')->whereNotNull('teacher_id')->first();
        $schedule = DB::table('schedules')->where('id', $header->schedules_id)->first();
        $studentId = DB::table('mapping_class_children')->where('class_id', $schedule->class_id)->value('student_id');
        DB::table('schedules')->where('id', $schedule->id)->update(['date' => "{$year}-12-15 10:00:00"]);
        DB::table('students')->where('id', $studentId)->update(['Quota' => 6, 'is_new' => 1]);

        $report = null;
        View::composer('staff.report.teacher.print', function ($view) use (&$report) { $report = $view->getData()['report']; });
        $fee = function (string $date) use ($studentId) {
            DB::table('transactions')->where('students_id', $studentId)->delete();
            DB::table('transactions')->insert([
                'students_id' => $studentId, 'transaction_date' => $date, 'payment_status' => 'Unpaid',
                'discount' => 0, 'price' => 1, 'desc' => '-', 'created_at' => now(), 'updated_at' => now(),
            ]);
        };

        foreach (["{$year}-12-10" => 1, ($year + 1).'-01-10' => 1, "{$year}-10-10" => 0] as $date => $expected) {
            $fee($date);
            $this->asRole('head')->post(route('head.report.teacher.print', 12))->assertOk();
            $this->assertCount($expected, $report, "fee on {$date}");
        }
    }
}
