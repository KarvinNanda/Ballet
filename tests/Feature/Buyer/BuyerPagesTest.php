<?php

namespace Tests\Feature\Buyer;

use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

class BuyerPagesTest extends FinanceTestCase
{
    public function test_list_shows_sell_for_items_in_stock_and_sold_out_otherwise(): void
    {
        $tutu = $this->stockItem('Buyx Tutu', 4, 'S');
        $shoes = $this->stockItem('Buyx Shoes', 0);

        $html = $this->asBuyer()->get(route('buyer', ['search' => 'Buyx']))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Items</h1>', $html);
        $row = $this->rowFor($html, 'Buyx Tutu');
        $this->assertStringContainsString('<td>4</td>', $row);
        $this->assertStringContainsString('<a href="'.route('buyingItem', $tutu->id).'" class="btn btn-sm btn-primary">Sell</a>', $row);

        $row = $this->rowFor($html, 'Buyx Shoes');
        $this->assertStringContainsString('<span class="status-badge status-badge-neutral">Sold out</span>', $row);
        $this->assertStringNotContainsString(route('buyingItem', $shoes->id), $row);
    }

    public function test_sort_applies_the_search_and_links_carry_it(): void
    {
        $this->stockItem('Buyx Tutu', 4);
        $this->stockItem('Buyx Leotard', 2);
        $this->stockItem('Other Buy Item', 9);

        $html = $this->asBuyer()->get(route('buyerSorting', ['value' => 'name', 'type' => 'asc', 'search' => 'Buyx']))
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('Other Buy Item', $html);
        $this->assertLessThan(strpos($html, 'Buyx Tutu'), strpos($html, 'Buyx Leotard'), 'name ascending');
        $this->assertStringContainsString('href="'.route('buyerSorting', ['value' => 'quantity', 'type' => 'desc', 'search' => 'Buyx']).'"', $html);
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asBuyer()->get(route('buyer', ['search' => 'zzz-no-such-item']))
            ->assertOk()->assertSeeText('No items found')->assertDontSee('<table', false);
    }

    public function test_sell_page_caps_the_quantity_at_the_stock_without_inline_script(): void
    {
        $stock = $this->stockItem('Buyx Tutu', 4, 'S');

        $html = $this->asBuyer()->get(route('buyingItem', $stock->id))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Sell · Buyx Tutu</h1>', $html);
        $this->assertStringContainsString('<a href="'.route('buyer').'" class="btn btn-outline-secondary">', $html);
        $this->assertStringContainsString('<dt>In stock</dt><dd>4</dd>', $html);
        $this->assertStringContainsString('action="'.route('buying', $stock->id).'"', $html);
        $this->assertStringContainsString('<input type="text" id="field-name" name="name" value="" class="form-control" required', $html);
        $this->assertStringContainsString('<input type="number" id="field-qty" name="qty" value="" class="form-control" min="1" max="4" required', $html);
        $this->assertStringContainsString('Record sale', $html);
        $this->assertStringNotContainsString('<script>', $html);
    }

    public function test_a_sold_out_item_page_shows_no_form(): void
    {
        $stock = $this->stockItem('Buyx Shoes', 0);

        $this->asBuyer()->get(route('buyingItem', $stock->id))->assertOk()
            ->assertSeeText('Sold out')
            ->assertDontSee('action="'.route('buying', $stock->id).'"', false)
            ->assertDontSee('name="qty"', false);
    }

    public function test_selling_more_than_in_stock_shows_the_error_on_the_quantity_field_and_changes_nothing(): void
    {
        $stock = $this->stockItem('Buyx Tutu', 4);
        $sellUrl = route('buyingItem', $stock->id);
        $action = route('buying', $stock->id);
        $reports = DB::table('report_stocks')->count();

        $html = $this->asBuyer()->get($sellUrl)->assertOk()->getContent();
        // Two are sold elsewhere after the page set max="4".
        DB::table('stocks')->where('id', $stock->id)->update(['quantity' => 2]);
        $fields = array_merge($this->formFields($html, $action), ['name' => 'Ibu Rina', 'qty' => '3']);

        $this->from($sellUrl)->post($action, $fields)->assertRedirect($sellUrl);
        // Render the page before any session assertion: TestResponse::session() restarts the session store, and
        // with JSON session serialization that drops the flashed error bag before the next request can show it.
        $page = $this->get($sellUrl)->assertOk()->getContent();

        $this->assertSame(2, (int) $stock->fresh()->quantity);
        $this->assertSame(0, DB::table('buyers')->where('stock_id', $stock->id)->count());
        $this->assertSame($reports, DB::table('report_stocks')->count());

        $this->assertStringContainsString('<div id="field-qty-error" class="invalid-feedback d-block">Only 2 left in stock.</div>', $page);
        // Shown on the field only: the old `error` flash alert would repeat the message.
        $this->assertSame(1, substr_count($page, 'Only 2 left in stock.'));
        $this->assertStringContainsString('name="name" value="Ibu Rina"', $page);
    }

    public function test_posting_the_rendered_sell_form_records_the_sale_and_returns_to_the_list(): void
    {
        $stock = $this->stockItem('Buyx Tutu', 4);
        $buyer = $this->newBuyer('Toko Kasir');
        $listUrl = route('buyer', ['search' => 'Buyx']);
        $action = route('buying', $stock->id);

        $html = $this->asBuyer($buyer)->from($listUrl)->get(route('buyingItem', $stock->id))->assertOk()->getContent();
        $fields = array_merge($this->formFields($html, $action), ['name' => 'Ibu Rina', 'qty' => '3']);

        $this->post($action, $fields)->assertSessionHasNoErrors()->assertRedirect($listUrl)->assertSessionHas('msg');

        $this->assertSame(1, (int) $stock->fresh()->quantity);
        $this->assertDatabaseHas('buyers', ['stock_id' => $stock->id, 'name' => 'Ibu Rina', 'qty' => 3, 'served_by' => 'Toko Kasir']);
    }

    public function test_a_refused_sale_keeps_the_list_url_as_return_url(): void
    {
        $stock = $this->stockItem('Buyx Tutu', 4);
        $listUrl = route('buyer', ['search' => 'Buyx']);
        $sellUrl = route('buyingItem', $stock->id);
        $action = route('buying', $stock->id);

        $html = $this->asBuyer()->from($listUrl)->get($sellUrl)->assertOk()->getContent();
        DB::table('stocks')->where('id', $stock->id)->update(['quantity' => 2]);
        $fields = array_merge($this->formFields($html, $action), ['name' => 'Ibu Rina', 'qty' => '3']);
        $this->assertSame($listUrl, $fields['return_url']);

        $this->from($sellUrl)->post($action, $fields)->assertRedirect($sellUrl);
        $page = $this->from($sellUrl)->get($sellUrl)->assertOk()->getContent();

        $this->assertStringContainsString('name="return_url" value="'.e($listUrl).'"', $page);
    }

    public function test_a_sold_out_page_still_shows_the_quantity_error_of_the_refused_sale(): void
    {
        $stock = $this->stockItem('Buyx Tutu', 1);
        $sellUrl = route('buyingItem', $stock->id);
        $action = route('buying', $stock->id);

        $html = $this->asBuyer()->get($sellUrl)->assertOk()->getContent();
        $fields = array_merge($this->formFields($html, $action), ['name' => 'Ibu Rina', 'qty' => '5']);
        $this->from($sellUrl)->post($action, $fields)->assertRedirect($sellUrl);
        // Another buyer takes the last one before the page reloads.
        DB::table('stocks')->where('id', $stock->id)->update(['quantity' => 0]);

        $page = $this->get($sellUrl)->assertOk()->getContent();

        $this->assertStringContainsString('Sold out', $page);
        $this->assertMatchesRegularExpression('/<div class="alert [^"]*" role="alert">\s*Only 1 left in stock\.\s*<\/div>/', $page);
        $this->assertLessThan(strpos($page, 'Sold out'), strpos($page, 'Only 1 left in stock.'), 'the message sits above the empty state');
    }
}
