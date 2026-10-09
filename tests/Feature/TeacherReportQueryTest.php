<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\TeacherReportQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TeacherReportQueryTest extends TestCase
{
    use RefreshDatabase;

    public function test_december_rows_are_found_and_both_routes_render(): void // finance copy was always empty
    {
        $year = now()->setTimezone('GMT+7')->year;
        $m = DB::table('mapping_class_children')->first();
        $teacherId = DB::table('mapping_class_teachers')->where('class_id', $m->class_id)->value('user_id')
            ?? User::where('role', 'teacher')->value('id');

        // is_new = 1 passes the report's student filter for every class type except Intensive/Pointe; Quota 1 passes those.
        DB::table('students')->where('id', $m->student_id)->update(['is_new' => 1, 'Quota' => 1]);
        $scheduleId = DB::table('schedules')->insertGetId(['class_id' => $m->class_id, 'date' => "{$year}-12-05 10:00:00", 'created_at' => now(), 'updated_at' => now()]);
        DB::table('header_absens')->insert(['schedules_id' => $scheduleId, 'teacher_id' => $teacherId, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('transactions')->insert([
            'students_id' => $m->student_id, 'class_transactions_id' => $m->class_id, 'transaction_date' => "{$year}-12-01",
            'payment_status' => 'Unpaid', 'discount' => 0, 'price' => 100, 'desc' => '-', 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->assertNotEmpty(TeacherReportQuery::rows(12));

        $this->actingAs(User::where('role', 'finance')->firstOrFail())->post(route('financeTeacherReport', 12))->assertOk();
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->actingAs(User::where('role', 'head')->firstOrFail())->post(route('head.report.teacher.print', 12))->assertOk();
    }
}
