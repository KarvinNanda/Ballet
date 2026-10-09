<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class ReportPagesTest extends StaffTestCase
{
    /** @return array<string, array{0: string}> */
    public static function roles(): array
    {
        return ['admin' => ['admin'], 'head' => ['head']];
    }

    /** Assert $html has a POST form to $action that opens in a new tab and carries a CSRF token. */
    private function assertPdfForm(string $html, string $action): void
    {
        $pattern = '/<form\b(?=[^>]*\saction="'.preg_quote(e($action), '/').'")(?=[^>]*\smethod="post")(?=[^>]*\starget="_blank")[^>]*>.*?<\/form>/is';
        $this->assertMatchesRegularExpression($pattern, $html, "no POST target=_blank form to {$action}");
        preg_match($pattern, $html, $m);
        $this->assertStringContainsString('name="_token"', $m[0]);
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_active_student_page_renders_a_course_select(string $role): void
    {
        $html = $this->asRole($role)->get(route("{$role}.report.active-student"))
            ->assertOk()->assertSee('Active students report')->assertSee('Opens the PDF in a new tab.')
            ->assertSee('Open report (PDF)')->getContent();

        $this->assertPdfForm($html, route("{$role}.report.active-student.print"));
        $this->assertMatchesRegularExpression('/<select\b[^>]*name="class"/', $html);
        $this->assertStringNotContainsString('<datalist', $html);
        $this->assertMatchesRegularExpression('/<option value="" selected>All courses<\/option>/', $html);
        $this->assertStringNotContainsString('Select…', $html);
        $course = DB::table('class_types')->orderBy('class_name')->value('class_name');
        $this->assertStringContainsString('<option value="'.e($course).'"', $html);
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_active_student_rendered_option_values_open_the_pdf(string $role): void
    {
        $html = $this->asRole($role)->get(route("{$role}.report.active-student"))->getContent();
        preg_match_all('/<option value="([^"]*)"/', $html, $m);
        $values = array_map('html_entity_decode', $m[1]);
        $this->assertContains('', $values);
        $course = DB::table('class_types')->orderBy('class_name')->value('class_name');
        $this->assertContains($course, $values);

        foreach (['', $course] as $value) {
            $this->asRole($role)->post(route("{$role}.report.active-student.print"), ['class' => $value])
                ->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_stock_page_renders_date_fields_defaulting_to_today(string $role): void
    {
        $today = now()->setTimezone('GMT+7')->toDateString();
        $html = $this->asRole($role)->get(route("{$role}.report.stock"))
            ->assertOk()->assertSee('Stock report')->assertSee('Open report (PDF)')->getContent();

        $this->assertPdfForm($html, route("{$role}.report.stock.print"));
        foreach (['start_date', 'end_date'] as $name) {
            $this->assertMatchesRegularExpression('/<input type="date"[^>]*name="'.$name.'" value="'.$today.'"[^>]*\srequired/', $html);
        }

        $this->asRole($role)->post(route("{$role}.report.stock.print"), ['start_date' => $today, 'end_date' => $today])
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_class_attendance_page_lists_classes_with_student_counts_from_the_controller(string $role): void
    {
        $data = null;
        View::composer('staff.report.class-attendence.index', function ($view) use (&$data) { $data = $view->getData()['data']; });

        DB::enableQueryLog();
        $html = $this->asRole($role)->get(route("{$role}.report.class"))
            ->assertOk()->assertSee('Class attendance report')->getContent();
        $countQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], 'mapping_class_children'))->count();
        DB::disableQueryLog();

        $this->assertNotEmpty($data);
        $this->assertLessThanOrEqual(1, $countQueries, 'student counts must come from the one list query, not one query per row');
        foreach ($data as $item) {
            $this->assertTrue(isset($item->students));
            $this->assertSame(DB::table('mapping_class_children')->where('class_id', $item->class_id)->count(), (int) $item->students);
        }

        $item = $data->first();
        $row = $this->rowFor($html, $item->teacher);
        $this->assertStringContainsString(e($item->class_name), $row);
        $this->assertMatchesRegularExpression('/<td[^>]*>\s*'.(int) $item->students.'\s*<\/td>/', $row);
        $this->assertPdfForm($row, route("{$role}.report.class.print", ['header' => $item->class_id, 'teacher' => $item->teacher]));
        $this->assertStringContainsString('btn-sm', $row);
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_class_attendance_page_shows_an_empty_state_without_attendance(string $role): void
    {
        DB::table('header_absens')->delete();

        $this->asRole($role)->get(route("{$role}.report.class"))
            ->assertOk()->assertSee('No attendance recorded yet')->assertDontSee('<table', false);
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_teacher_page_lists_months_with_report_forms(string $role): void
    {
        // The seed has no month that qualifies (needs a student with enough quota or a new one), so make one.
        $classId = DB::table('schedules')->join('header_absens', 'header_absens.schedules_id', 'schedules.id')->value('schedules.class_id');
        DB::table('students')->whereIn('id', DB::table('mapping_class_children')->where('class_id', $classId)->pluck('student_id'))
            ->update(['Quota' => 6, 'is_new' => 1]);

        $data = null;
        View::composer('staff.report.teacher.index', function ($view) use (&$data) { $data = $view->getData()['data']; });

        $html = $this->asRole($role)->get(route("{$role}.report.teacher"))
            ->assertOk()->assertSee('Teacher reward report')->getContent();

        $this->assertNotEmpty($data);
        $item = $data->first();
        $row = $this->rowFor($html, $item->month);
        $this->assertPdfForm($row, route("{$role}.report.teacher.print", $item->month_num));
        $this->assertStringContainsString('Report', $row);
    }

    /** @dataProvider roles */
    #[\PHPUnit\Framework\Attributes\DataProvider('roles')]
    public function test_teacher_page_shows_an_empty_state_without_months(string $role): void
    {
        DB::table('header_absens')->delete();

        $this->asRole($role)->get(route("{$role}.report.teacher"))
            ->assertOk()->assertSee('No months with attendance yet')->assertDontSee('<table', false);
    }
}
