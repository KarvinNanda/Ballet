<?php

namespace Tests\Feature;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

/**
 * Sortable headers (x-sort-th), the sort surviving a search/status change, and the empty-state reset button.
 */
class SortAndFilterTest extends FinanceTestCase
{
    /** The opening tags of every <th> on the page. */
    private function thTags(string $html): array
    {
        preg_match_all('/<th\b[^>]*>/', $html, $m);

        return $m[0];
    }

    private function sortedThTags(string $html): array
    {
        return array_values(array_filter($this->thTags($html), fn ($tag) => str_contains($tag, 'aria-sort')));
    }

    public function test_staff_student_sort_page_marks_only_the_active_column(): void
    {
        $html = $this->asRole('head')->get(route('head.student.sort', ['name', 'asc']))->assertOk()->getContent();

        $sorted = $this->sortedThTags($html);
        $this->assertCount(1, $sorted);
        $this->assertStringContainsString('aria-sort="ascending"', $sorted[0]);
        $this->assertMatchesRegularExpression('/<th scope="col"\s+aria-sort="ascending"\s*>\s*<a href="[^"]+" class="sort-link">Name\s*<i class="bi bi-arrow-up" aria-hidden="true"><\/i><\/a>/', $html);
        $this->assertSame(1, substr_count($html, 'bi-arrow-up'));
        $this->assertSame(0, substr_count($html, 'bi-arrow-down'));
        // The Birthday header is sortable but not active, and keeps its link without aria-sort or arrow.
        $this->assertMatchesRegularExpression('/<th scope="col"\s*>\s*<a href="[^"]*student\/sorting\/dob\/desc[^"]*" class="sort-link">Birthday<\/a>/', $html);
    }

    public function test_index_pages_have_no_aria_sort_and_keep_extra_th_attributes(): void
    {
        $html = $this->asRole('head')->get(route('head.student.index'))->assertOk()->getContent();

        $this->assertSame([], $this->sortedThTags($html));
        $this->assertStringNotContainsString('bi-arrow-up', $html);
        // A th that is not sortable keeps its own markup.
        $this->assertStringContainsString('<th scope="col" class="d-none d-lg-table-cell">Phone</th>', $html);
    }

    public function test_buyer_sort_desc_is_marked_descending(): void
    {
        $this->stockItem('Ballet Shoes', 5, 'M');
        $this->stockItem('Tutu', 3, 'S');

        $html = $this->asBuyer()->get(route('buyerSorting', ['name', 'desc']))->assertOk()->getContent();

        $sorted = $this->sortedThTags($html);
        $this->assertCount(1, $sorted);
        $this->assertStringContainsString('aria-sort="descending"', $sorted[0]);
        $this->assertMatchesRegularExpression('/class="sort-link">Name\s*<i class="bi bi-arrow-down" aria-hidden="true"><\/i><\/a>/', $html);
    }

    public function test_every_sortable_list_marks_its_active_column(): void
    {
        $this->stockItem('Ballet Shoes', 5, 'M');
        $this->transaction(['student' => 'Sort Kid']);

        $cases = [
            ['head', route('head.student.sort', ['dob', 'desc']), 'descending', 'Birthday'],
            ['head', route('head.transaction.sort', ['price', 'asc']), 'ascending', 'Total'],
            ['head', route('head.class.sort', ['status', 'desc']), 'descending', 'Status'],
            ['head', route('head.stock.sort', ['quantity', 'asc']), 'ascending', 'Quantity'],
            ['finance', route('financeTransactionSorting', ['payment_status']), 'ascending', 'Status'],
            ['finance', route('financeStockViewSorting', ['size', 'desc']), 'descending', 'Size'],
        ];

        foreach ($cases as [$role, $url, $aria, $label]) {
            $html = $this->asRole($role)->get($url)->assertOk()->getContent();
            $sorted = $this->sortedThTags($html);

            $this->assertCount(1, $sorted, $url);
            $this->assertStringContainsString('aria-sort="'.$aria.'"', $sorted[0], $url);
            $this->assertMatchesRegularExpression('/aria-sort="'.$aria.'"\s*>\s*<a [^>]*class="sort-link">'.$label.'\s*<i class="bi bi-arrow-/', $html, $url);
        }
    }

    public function test_filter_form_and_status_links_stay_on_the_sort_page_and_reset_goes_to_the_index(): void
    {
        $sortUrl = route('head.transaction.sort', ['price', 'asc']);

        $html = $this->asRole('head')->get($sortUrl.'?status=Paid&search=Kid&page=2')->assertOk()->getContent();

        $this->assertStringContainsString('method="GET" action="'.$sortUrl.'"', $html);
        // A status link keeps the sort path and the search, and never carries `page`.
        $this->assertStringContainsString('href="'.e($sortUrl.'?search=Kid&status=Unpaid').'"', $html);
        $this->assertStringContainsString('href="'.e($sortUrl.'?search=Kid').'"', $html);
        preg_match('/<form method="GET".*?<\/form>/s', $html, $bar);
        $this->assertStringNotContainsString('page=', $bar[0]);
        $this->assertStringContainsString('<a href="'.route('head.transaction.index').'" class="btn btn-outline-secondary">Reset</a>', $html);
    }

    public function test_status_links_on_the_index_stay_on_the_index(): void
    {
        $index = route('head.transaction.index');

        $html = $this->asRole('head')->get($index.'?status=Paid')->assertOk()->getContent();

        $this->assertStringContainsString('method="GET" action="'.$index.'"', $html);
        $this->assertStringContainsString('href="'.e($index.'?status=Unpaid').'"', $html);
        $this->assertStringContainsString('<a href="'.$index.'" class="btn btn-outline-secondary">Reset</a>', $html);
    }

    public function test_finance_sort_pages_keep_their_path_and_reset_to_the_index(): void
    {
        $sortUrl = route('financeTransactionSorting', ['price']);

        $html = $this->asRole('finance')->get($sortUrl.'?status=Paid')->assertOk()->getContent();

        $this->assertStringContainsString('method="GET" action="'.$sortUrl.'"', $html);
        $this->assertStringContainsString('href="'.$sortUrl.'"', $html); // Unpaid is the default: no status in its link
        $this->assertStringContainsString('<a href="'.route('financeTransaction').'" class="btn btn-outline-secondary">Reset</a>', $html);

        $stockSort = route('financeStockViewSorting', ['name', 'asc']);
        $html = $this->get($stockSort)->assertOk()->getContent();
        $this->assertStringContainsString('method="GET" action="'.$stockSort.'"', $html);
        $this->assertStringContainsString('<a href="'.route('finance').'" class="btn btn-outline-secondary">Reset</a>', $html);

        $this->stockItem('Ballet Shoes', 1);
        $buyerSort = route('buyerSorting', ['name', 'asc']);
        $html = $this->asBuyer()->get($buyerSort)->assertOk()->getContent();
        $this->assertStringContainsString('method="GET" action="'.$buyerSort.'"', $html);
        $this->assertStringContainsString('<a href="'.route('buyer').'" class="btn btn-outline-secondary">Reset</a>', $html);
    }

    public function test_searching_on_a_sort_page_filters_and_keeps_the_order(): void
    {
        Student::factory()->create(['LongName' => 'Abe Sortable', 'Status' => 'aktif']);
        Student::factory()->create(['LongName' => 'Zed Sortable', 'Status' => 'aktif']);
        Student::factory()->create(['LongName' => 'Mid Unrelated', 'Status' => 'aktif']);
        $sortUrl = route('head.student.sort', ['name', 'desc']);

        $page = $this->asRole('head')->get($sortUrl)->assertOk()->getContent();
        preg_match('/<form method="GET" action="([^"]+)" role="search"/', $page, $form);
        $this->assertSame($sortUrl, html_entity_decode($form[1]));

        // What the browser does on Apply: GET the form action with the fields.
        $this->get($form[1].'?keyword=Sortable&status=all')->assertOk()
            ->assertSeeInOrder(['Zed Sortable', 'Abe Sortable'])
            ->assertDontSee('Mid Unrelated');

        $this->get(route('head.student.sort', ['name', 'asc']).'?keyword=Sortable&status=all')->assertOk()
            ->assertSeeInOrder(['Abe Sortable', 'Zed Sortable']);
    }

    public function test_a_search_with_no_match_offers_a_reset_to_the_index(): void
    {
        $html = $this->asRole('head')->get(route('head.student.index', ['keyword' => 'zzzqqq-no-such']))->assertOk()->getContent();

        $this->assertStringContainsString('No students found', $html);
        $this->assertStringContainsString('<a href="'.route('head.student.index').'" class="btn btn-outline-secondary">Reset filters</a>', $html);

        // A non-default status is a filter too, even without a keyword.
        Student::query()->update(['Status' => 'aktif']);
        $html = $this->get(route('head.student.index', ['status' => 'trial']))->assertOk()->getContent();
        $this->assertStringContainsString('Reset filters', $html);
    }

    public function test_the_keyword_zero_counts_as_a_filter(): void
    {
        $html = $this->asRole('head')->get(route('head.teacher.index', ['search' => '0']))->assertOk()->getContent();

        $this->assertStringContainsString('No teachers found', $html);
        $this->assertStringContainsString('Reset search', $html);
    }

    public function test_an_empty_list_without_a_filter_has_no_reset_button(): void
    {
        // Transactions list only active students: with none, the unfiltered list is empty.
        Student::query()->update(['Status' => 'non-aktif']);
        $html = $this->asRole('head')->get(route('head.transaction.index'))->assertOk()->getContent();
        $this->assertStringContainsString('No transactions found', $html);
        $this->assertStringNotContainsString('Reset filters', $html);
        // The filter bar's own Reset is not the empty-state button.
        $this->assertStringContainsString('class="btn btn-outline-secondary">Reset</a>', $html);

        $html = $this->get(route('head.transaction.index', ['status' => 'Paid']))->assertOk()->getContent();
        $this->assertStringContainsString('Reset filters', $html);
        $html = $this->get(route('head.transaction.index', ['search' => 'x']))->assertOk()->getContent();
        $this->assertStringContainsString('Reset filters', $html);

        // Head admin list: no admins at all.
        User::where('role', 'admin')->delete();
        $html = $this->get(route('headAdminPage'))->assertOk()->getContent();
        $this->assertStringContainsString('No admin accounts found', $html);
        $this->assertStringNotContainsString('Reset search', $html);
        $this->assertStringContainsString('Reset search', $this->get(route('headAdminPage', ['search' => 'x']))->getContent());

        // Finance accounts.
        User::where('role', 'finance')->delete();
        $html = $this->get(route('head.finance.index'))->assertOk()->getContent();
        $this->assertStringContainsString('No finance accounts found', $html);
        $this->assertStringNotContainsString('Reset search', $html);
        $this->assertStringContainsString('Reset search', $this->get(route('head.finance.index', ['search' => 'x']))->getContent());
    }

    public function test_switch_page_search_with_no_match_offers_a_reset(): void
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $switch = route('head.teacher.switch', $teacher);

        $html = $this->asRole('head')->get($switch.'?search=zzzqqq-no-such')->assertOk()->getContent();

        $this->assertStringContainsString('No other teachers found', $html);
        $this->assertStringContainsString('<a href="'.$switch.'" class="btn btn-outline-secondary">Reset search</a>', $html);
    }

    public function test_switch_page_with_no_other_teacher_and_no_search_has_no_reset_button(): void
    {
        $teachers = User::where('role', 'teacher')->get();
        $last = $teachers->first();
        DB::table('users')->where('role', 'teacher')->where('id', '!=', $last->id)->update(['role' => 'admin']);

        $html = $this->asRole('head')->get(route('head.teacher.switch', $last))->assertOk()->getContent();

        $this->assertStringContainsString('No other teachers found', $html);
        $this->assertStringNotContainsString('Reset search', $html);
    }

    public function test_stock_empty_states_follow_the_same_rule(): void
    {
        DB::table('stocks')->delete();

        $html = $this->asRole('head')->get(route('head.stock.index'))->assertOk()->getContent();
        $this->assertStringContainsString('No stock items found', $html);
        $this->assertStringNotContainsString('Reset search', $html);
        $this->assertStringContainsString('Reset search', $this->get(route('head.stock.index', ['search' => 'x']))->getContent());
        $this->assertStringContainsString('Reset search', $this->get(route('head.stock.sort', ['name', 'asc', 'search' => 'x']))->getContent());

        $html = $this->asRole('finance')->get(route('finance'))->assertOk()->getContent();
        $this->assertStringNotContainsString('Reset search', $html);
        $this->assertStringContainsString('Reset search', $this->get(route('finance', ['search' => 'x']))->getContent());

        $html = $this->asBuyer()->get(route('buyer'))->assertOk()->getContent();
        $this->assertStringContainsString('No items found', $html);
        $this->assertStringNotContainsString('Reset search', $html);
        $this->assertStringContainsString('Reset search', $this->get(route('buyer', ['search' => 'x']))->getContent());
    }

    public function test_finance_transaction_default_status_is_not_a_filter(): void
    {
        DB::table('transactions')->update(['payment_status' => 'Paid']);

        $html = $this->asRole('finance')->get(route('financeTransaction'))->assertOk()->getContent();
        $this->assertStringContainsString('No transactions found', $html);
        $this->assertStringNotContainsString('Reset filters', $html);

        $this->assertStringContainsString('Reset filters', $this->get(route('financeTransaction', ['status' => 'all', 'search' => 'zzz']))->getContent());
        // Paid is not the default, so the filtered-but-empty case offers a reset.
        DB::table('transactions')->update(['payment_status' => 'Unpaid']);
        $this->assertStringContainsString('Reset filters', $this->get(route('financeTransaction', ['status' => 'Paid']))->getContent());
    }

    public function test_class_lists_show_reset_only_while_filtering(): void
    {
        $html = $this->asRole('head')->get(route('head.class.index', ['keyword' => 'zzzqqq-no-such']))->assertOk()->getContent();
        $this->assertStringContainsString('No classes found', $html);
        $this->assertStringContainsString('Reset filters', $html);

        DB::table('class_transactions')->update(['Status' => 'non-aktif']);
        $html = $this->get(route('head.class.index', ['status' => 'aktif']))->assertOk()->getContent();
        $this->assertStringContainsString('Reset filters', $html);
    }

    public function test_add_student_and_frozen_class_lists_offer_a_reset_while_searching(): void
    {
        $classId = DB::table('class_transactions')->where('is_freeze', '!=', 1)->value('id');

        $html = $this->asRole('head')->get(route('head.class.student.create', $classId).'?keyword=zzzqqq-no-such')->assertOk()->getContent();
        $this->assertStringContainsString('No student to add', $html);
        $this->assertStringContainsString('Reset search', $html);

        $html = $this->get(route('head.class.freeze.index', ['keyword' => 'zzzqqq-no-such']))->assertOk()->getContent();
        $this->assertStringContainsString('No frozen classes found', $html);
        $this->assertStringContainsString('Reset search', $html);
    }
}
