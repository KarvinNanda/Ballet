<?php

namespace Tests\Feature\Finance;

use Illuminate\Support\Facades\DB;

class FinanceStockPagesTest extends FinanceTestCase
{
    public function test_list_shows_items_with_a_record_stock_in_action(): void
    {
        $tutu = $this->stockItem('Fintu Tutu Pink', 7, 'S');
        $this->stockItem('Other Fin Item', 2);

        $html = $this->asRole('finance')->get(route('finance', ['search' => 'Fintu']))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Stock</h1>', $html);
        $this->assertStringContainsString('name="search" value="Fintu"', $html);
        $row = $this->rowFor($html, 'Fintu Tutu Pink');
        $this->assertStringContainsString('<td>S</td>', $row);
        $this->assertStringContainsString('<td>7</td>', $row);
        $this->assertStringContainsString('<a href="'.route('in', $tutu).'" class="btn btn-sm btn-primary">Record stock in</a>', $row);
        $this->assertStringNotContainsString('Other Fin Item', $html);
    }

    public function test_sort_applies_the_search_and_links_carry_it(): void
    {
        // Leotard first, so the default id-desc order (Tutu, Leotard) differs from name ascending.
        $this->stockItem('Fintu Leotard', 3);
        $this->stockItem('Fintu Tutu Pink', 7);
        $this->stockItem('Other Fin Item', 2);

        $html = $this->asRole('finance')->get(route('financeStockViewSorting', ['value' => 'name', 'sort' => 'asc', 'search' => 'Fintu']))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('Other Fin Item', $html);
        $this->assertLessThan(strpos($html, 'Fintu Tutu Pink'), strpos($html, 'Fintu Leotard'), 'name ascending');

        $desc = $this->asRole('finance')->get(route('financeStockViewSorting', ['value' => 'name', 'sort' => 'desc', 'search' => 'Fintu']))
            ->assertOk()->getContent();
        $this->assertLessThan(strpos($desc, 'Fintu Leotard'), strpos($desc, 'Fintu Tutu Pink'), 'name descending');
        // The next header click sorts the other way and keeps the search.
        $this->assertStringContainsString('href="'.route('financeStockViewSorting', ['value' => 'quantity', 'sort' => 'desc', 'search' => 'Fintu']).'"', $html);

        $this->asRole('finance')->get(route('finance', ['search' => 'Fintu']))
            ->assertSee('href="'.route('financeStockViewSorting', ['value' => 'name', 'sort' => 'asc', 'search' => 'Fintu']).'"', false);
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asRole('finance')->get(route('finance', ['search' => 'zzz-no-such-item']))
            ->assertOk()->assertSeeText('No stock items found')->assertDontSee('<table', false);
    }

    public function test_record_stock_in_page_shows_the_item_and_a_required_quantity_field(): void
    {
        $stock = $this->stockItem('Fintu Tutu Pink', 7, 'S');
        DB::table('buyers')->insert([
            'stock_id' => $stock->id, 'name' => 'Ibu Rina', 'qty' => 2, 'served_by' => 'Toko Kasir',
            'created_at' => '2026-10-01 10:00:00', 'updated_at' => '2026-10-01 10:00:00',
        ]);

        $html = $this->asRole('finance')->get(route('in', $stock))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Record stock in · Fintu Tutu Pink</h1>', $html);
        $this->assertStringContainsString('<a href="'.route('finance').'" class="btn btn-outline-secondary">', $html);
        $this->assertStringContainsString('<dt>Size</dt><dd>S</dd>', $html);
        $this->assertStringContainsString('<dt>Quantity</dt><dd>7</dd>', $html);
        $this->assertStringContainsString('action="'.route('makeReport', [$stock, 'in']).'"', $html);
        $this->assertStringContainsString('<input type="number" id="field-in_out" name="in_out" value="" class="form-control" min="1" required', $html);
        $this->assertStringContainsString('Quantity in', $html);
        $this->assertStringNotContainsString('(Optional)', $html);

        $row = $this->rowFor($html, 'Ibu Rina');
        $this->assertStringContainsString('<td>2</td>', $row);
        $this->assertStringContainsString('<td>01 Oct 2026</td>', $row);
        $this->assertStringContainsString('<td>Toko Kasir</td>', $row);
    }

    public function test_record_stock_in_page_without_purchases_shows_the_empty_state(): void
    {
        $stock = $this->stockItem('Fintu Leotard', 3);

        $this->asRole('finance')->get(route('in', $stock))->assertOk()
            ->assertSeeText('Purchase history')->assertSeeText('No purchases yet')->assertDontSee('<table', false);
    }

    public function test_posting_the_rendered_form_records_stock_in_and_returns_to_the_list(): void
    {
        $stock = $this->stockItem('Fintu Tutu Pink', 7);
        $listUrl = route('finance', ['search' => 'Fintu']);
        $action = route('makeReport', [$stock, 'in']);
        $reports = DB::table('report_stocks')->count();

        $html = $this->asRole('finance')->from($listUrl)->get(route('in', $stock))->assertOk()->getContent();
        $fields = array_merge($this->formFields($html, $action), ['in_out' => '5']);

        $this->post($action, $fields)->assertSessionHasNoErrors()->assertRedirect($listUrl);

        $this->assertSame(12, (int) $stock->fresh()->quantity);
        $this->assertSame($reports + 1, DB::table('report_stocks')->count());
        $this->assertSame(5, (int) DB::table('report_stocks')->orderByDesc('id')->value('in'));
    }

    public function test_the_unused_stock_out_page_route_is_gone(): void
    {
        // It rendered finance/in-out without the purchase list (500); stock out is recorded by the buyer sale.
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('out'));
        $stock = $this->stockItem('Out Leotard', 3);

        $this->asRole('finance')->post('/finance/out/'.$stock->id)->assertNotFound();
        $this->assertSame(3, (int) $stock->fresh()->quantity);
    }
}
