<?php

namespace Tests\Feature\Gaps;

use App\Models\Student;
use Tests\Feature\Finance\FinanceTestCase;

/**
 * Page 2 must carry the same search, status and sort as page 1: the page-2 link says so, and following it still filters.
 * Rows are created oldest first and every list orders newest first, so the OLDEST matching row is the only one on page 2.
 */
class PaginationKeepsFiltersTest extends FinanceTestCase
{
    /** Query string of the "page=2" link in $html, decoded; also returns the path. */
    private function pageTwoLink(string $html): array
    {
        preg_match_all('/href="([^"]*[?&](?:amp;)?page=2[^"]*)"/', $html, $m);
        $this->assertNotEmpty($m[1], 'page 1 has no link to page 2');
        $url = html_entity_decode($m[1][0]);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        return [parse_url($url, PHP_URL_PATH), $query, $url];
    }

    public function test_staff_student_list_keeps_keyword_and_status(): void
    {
        $this->asRole('head');
        Student::factory()->create(['LongName' => 'Quokka Inactive', 'Status' => 'non-aktif']);
        Student::factory()->create(['LongName' => 'Zebra Inactive', 'Status' => 'non-aktif']);
        for ($i = 1; $i <= 21; $i++) {
            Student::factory()->create(['LongName' => sprintf('Zebra Kid %02d', $i), 'Status' => 'aktif']);
        }
        Student::factory()->create(['LongName' => 'Quokka Active', 'Status' => 'aktif']);

        $page1 = $this->get(route('head.student.index', ['keyword' => 'Zebra', 'status' => 'aktif']))->assertOk();
        [$path, $query, $url] = $this->pageTwoLink($page1->getContent());
        $this->assertSame(route('head.student.index', [], false), $path);
        $this->assertSame('Zebra', $query['keyword']);
        $this->assertSame('aktif', $query['status']);

        $page2 = $this->get($url)->assertOk();
        $page2->assertSee('Zebra Kid 01')
            ->assertDontSee('Zebra Kid 02')
            ->assertDontSee('Zebra Inactive')
            ->assertDontSee('Quokka');
    }

    public function test_staff_transaction_sort_page_keeps_search_status_and_sort(): void
    {
        $this->asRole('head');
        $this->transaction(['student' => 'Zed Decoy Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02', 'price' => 999999]);
        $this->transaction(['student' => 'Quokka Decoy', 'price' => 1]);
        for ($i = 1; $i <= 21; $i++) {
            $this->transaction(['student' => sprintf('Zed Kid %02d', $i), 'price' => $i * 1000]);
        }

        $page1 = $this->get(route('head.transaction.sort', ['price', 'asc', 'search' => 'Zed', 'status' => 'Unpaid']))->assertOk();
        [$path, $query, $url] = $this->pageTwoLink($page1->getContent());
        $this->assertSame(route('head.transaction.sort', ['price', 'asc'], false), $path);
        $this->assertSame('Zed', $query['search']);
        $this->assertSame('Unpaid', $query['status']);

        // Sorted by price ascending, the dearest of the 21 matching rows is the one on page 2; id order would show Zed Kid 01 there.
        $this->get($url)->assertOk()
            ->assertSee('Zed Kid 21')
            ->assertDontSee('Zed Kid 01')
            ->assertDontSee('Zed Decoy Paid')
            ->assertDontSee('Quokka Decoy');
    }

    public function test_finance_transaction_list_keeps_search_and_status_all(): void
    {
        $this->asRole('finance');
        $this->transaction(['student' => 'Zed Kid 00 Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);
        for ($i = 1; $i <= 20; $i++) {
            $this->transaction(['student' => sprintf('Zed Kid %02d', $i)]);
        }
        $this->transaction(['student' => 'Quokka Decoy']);

        $page1 = $this->get(route('financeTransaction', ['search' => 'Zed', 'status' => 'all']))->assertOk();
        [$path, $query, $url] = $this->pageTwoLink($page1->getContent());
        $this->assertSame(route('financeTransaction', [], false), $path);
        $this->assertSame('Zed', $query['search']);
        $this->assertSame('all', $query['status']);

        // The only row on page 2 is the oldest one, and it is Paid: the default status (Unpaid) would not list it.
        $this->get($url)->assertOk()
            ->assertSee('Zed Kid 00 Paid')
            ->assertDontSee('Zed Kid 01')
            ->assertDontSee('Quokka Decoy');
    }

    public function test_buyer_sort_page_keeps_search_and_sort(): void
    {
        $this->stockItem('Quokka Decoy', 5);
        for ($i = 1; $i <= 6; $i++) {
            $this->stockItem("Tutu {$i}", 5);
        }
        $this->asBuyer();

        $page1 = $this->get(route('buyerSorting', ['name', 'asc', 'search' => 'Tutu']))->assertOk();
        [$path, $query, $url] = $this->pageTwoLink($page1->getContent());
        $this->assertSame(route('buyerSorting', ['name', 'asc'], false), $path);
        $this->assertSame('Tutu', $query['search']);

        // Name ascending puts Tutu 6 on page 2; id order would put Tutu 1 there.
        $this->get($url)->assertOk()
            ->assertSee('Tutu 6')
            ->assertDontSee('Tutu 1')
            ->assertDontSee('Quokka Decoy');
    }
}
