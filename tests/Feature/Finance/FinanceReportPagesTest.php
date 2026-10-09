<?php

namespace Tests\Feature\Finance;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;

class FinanceReportPagesTest extends FinanceTestCase
{
    /** Assert $html has a POST form to $action that opens in a new tab and carries a CSRF token. */
    private function assertPdfForm(string $html, string $action): void
    {
        $pattern = '/<form\b(?=[^>]*\saction="'.preg_quote(e($action), '/').'")(?=[^>]*\smethod="post")(?=[^>]*\starget="_blank")[^>]*>.*?<\/form>/is';
        $this->assertMatchesRegularExpression($pattern, $html, "no POST target=_blank form to {$action}");
        preg_match($pattern, $html, $m);
        $this->assertStringContainsString('name="_token"', $m[0]);
    }

    /** @return list<string> the option values of <select name="$name"> */
    private function optionValues(string $html, string $name): array
    {
        $this->assertMatchesRegularExpression('/<select\b[^>]*name="'.$name.'"[^>]*>.*?<\/select>/s', $html);
        preg_match('/<select\b[^>]*name="'.$name.'"[^>]*>(.*?)<\/select>/s', $html, $select);
        preg_match_all('/<option value="([^"]*)"/', $select[1], $options);

        return array_map(fn (string $value) => html_entity_decode($value, ENT_QUOTES), $options[1]);
    }

    public function test_stock_report_page_posts_dates_to_the_print_route_in_a_new_tab(): void
    {
        $today = now()->setTimezone('GMT+7')->toDateString();

        $html = $this->asRole('finance')->get(route('financeStockReport'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Stock report</h1>', $html);
        $this->assertStringContainsString('Open report (PDF)', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertPdfForm($html, route('financeStockPrintReport'));
        foreach (['start_date', 'end_date'] as $name) {
            $this->assertMatchesRegularExpression('/<input type="date"[^>]*name="'.$name.'" value="'.$today.'"[^>]*\srequired/', $html);
        }

        $this->post(route('financeStockPrintReport'), ['start_date' => $today, 'end_date' => $today])
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_student_report_page_offers_status_and_course_selects(): void
    {
        $html = $this->asRole('finance')->get(route('financeStudentReportPage'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Active student finance report</h1>', $html);
        $this->assertStringContainsString('Open report (PDF)', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('<datalist', $html);
        $this->assertPdfForm($html, route('financeStudentReport'));
        $this->assertStringContainsString('<option value="" selected>All statuses</option>', $html);
        $this->assertStringContainsString('<option value="" selected>All courses</option>', $html);
        $this->assertStringNotContainsString('Select…', $html);
    }

    public function test_student_report_rendered_option_values_open_the_pdf(): void
    {
        $html = $this->asRole('finance')->get(route('financeStudentReportPage'))->assertOk()->getContent();

        $this->assertSame(['', 'Paid', 'Unpaid'], $this->optionValues($html, 'status'));
        $courses = $this->optionValues($html, 'class');
        $course = DB::table('class_types')->orderBy('id')->value('class_name');
        $this->assertContains('', $courses);
        $this->assertContains($course, $courses);

        foreach ([['', ''], ['Paid', ''], ['Unpaid', $course]] as [$status, $class]) {
            $this->post(route('financeStudentReport'), ['status' => $status, 'class' => $class])
                ->assertOk()->assertHeader('content-type', 'application/pdf');
        }
    }

    public function test_teacher_report_page_lists_months_with_report_forms(): void
    {
        // The seed has no month that qualifies (needs a student with enough quota or a new one), so make one.
        $classId = DB::table('schedules')->join('header_absens', 'header_absens.schedules_id', 'schedules.id')->value('schedules.class_id');
        DB::table('students')->whereIn('id', DB::table('mapping_class_children')->where('class_id', $classId)->pluck('student_id'))
            ->update(['Quota' => 6, 'is_new' => 1]);

        $data = null;
        View::composer('finance.report.teacher.index', function ($view) use (&$data) { $data = $view->getData()['data']; });

        $html = $this->asRole('finance')->get(route('financeTeacherReportPage'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Teacher attendance report</h1>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertNotEmpty($data);
        $item = $data->first();
        $row = $this->rowFor($html, $item->month);
        $this->assertPdfForm($row, route('financeTeacherReport', $item->month_num));
        $this->assertStringContainsString('btn-sm', $row);
    }

    public function test_teacher_report_page_shows_an_empty_state_without_months(): void
    {
        DB::table('header_absens')->delete();

        $this->asRole('finance')->get(route('financeTeacherReportPage'))
            ->assertOk()->assertSeeText('No months with attendance yet')->assertDontSee('<table', false);
    }
}
