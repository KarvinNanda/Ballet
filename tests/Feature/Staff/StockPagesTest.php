<?php

namespace Tests\Feature\Staff;

use App\Models\Stock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class StockPagesTest extends StaffTestCase
{
    /** A new item: the newest id, so it is on page 1 of the list (id desc, 5 per page). */
    private function item(array $attributes = []): Stock
    {
        $id = DB::table('stocks')->insertGetId(array_merge(['name' => 'Zz Leotard', 'size' => 'S', 'quantity' => 4], $attributes));

        return Stock::findOrFail($id);
    }

    public function test_sort_links_carry_the_search(): void
    {
        $this->asRole('head')->get(route('head.stock.index', ['search' => 'Kaos']))->assertOk()
            ->assertSee(route('head.stock.sort', ['column' => 'quantity', 'direction' => 'asc', 'search' => 'Kaos']), false)
            ->assertSee(route('head.stock.sort', ['column' => 'name', 'direction' => 'asc', 'search' => 'Kaos']), false);
    }

    public function test_sort_keeps_the_search_filter(): void
    {
        $this->asRole('admin')->get(route('admin.stock.sort', ['column' => 'name', 'direction' => 'asc', 'search' => 'Kaos']))
            ->assertOk()->assertSee('Kaos Kaki Ballet')->assertDontSee('Sepatu Ballet');
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asRole('admin')->get(route('admin.stock.index', ['search' => 'zzz-nothing']))
            ->assertOk()->assertSeeText('No stock items found')->assertDontSee('<table', false);
    }

    public function test_admin_sees_no_add_update_or_delete(): void
    {
        $stock = $this->item();
        $html = $this->asRole('admin')->get(route('admin.stock.index'))->assertOk()->getContent();

        $this->assertStringContainsString('Zz Leotard', $html);
        $this->assertStringNotContainsString(route('admin.stock.create'), $html);
        $this->assertStringNotContainsString(route('admin.stock.edit', $stock), $html);
        $this->assertStringNotContainsString(route('admin.stock.destroy', $stock), $html);
        $this->assertStringNotContainsString('data-confirm', $html);
    }

    public function test_head_row_has_update_and_a_confirmed_delete(): void
    {
        $stock = $this->item();
        $row = $this->rowFor($this->asRole('head')->get(route('head.stock.index'))->getContent(), 'Zz Leotard');

        $this->assertStringContainsString('<td>S</td>', $row);
        $this->assertStringContainsString('<td>4</td>', $row);
        $this->assertStringContainsString('href="'.route('head.stock.edit', $stock).'"', $row);
        $this->assertStringContainsString('action="'.route('head.stock.destroy', $stock).'" data-confirm="Delete Zz Leotard (S)? This cannot be undone."', $row);
    }

    public function test_add_page_allows_a_zero_quantity(): void
    {
        $this->asRole('head')->get(route('head.stock.create'))->assertOk()
            ->assertSee('<h1 class="page-title">Add stock item</h1>', false)
            ->assertSee('<input type="number" id="field-inputQty" name="inputQty" value="" class="form-control" min="0" required', false);
    }

    public function test_update_page_lists_purchases(): void
    {
        $buyer = DB::table('buyers')->orderBy('id')->first();
        $html = $this->asRole('head')->get(route('head.stock.edit', $buyer->stock_id))->assertOk()->getContent();
        $row = $this->rowFor($html, $buyer->name);

        $this->assertStringContainsString('Purchase history', $html);
        $this->assertStringContainsString('<td>'.$buyer->qty.'</td>', $row);
        $this->assertStringContainsString('<td>'.Carbon::parse($buyer->created_at)->format('d M Y').'</td>', $row);
        $this->assertStringContainsString('<td>'.$buyer->served_by.'</td>', $row);
    }

    public function test_update_page_without_purchases_says_so(): void
    {
        $stock = $this->item();
        $this->asRole('head')->get(route('head.stock.edit', $stock))->assertOk()
            ->assertSeeText('No purchases yet')
            ->assertSee('<h1 class="page-title">Update stock item</h1>', false);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_every_column(): void
    {
        $stock = Stock::orderBy('id')->firstOrFail();
        $this->assertRoundTrip($stock);
    }

    public function test_a_sold_out_item_can_be_saved_unchanged(): void
    {
        $this->assertRoundTrip($this->item(['quantity' => 0]));
    }

    private function assertRoundTrip(Stock $stock): void
    {
        $before = (array) DB::table('stocks')->where('id', $stock->id)->first();

        $update = route('head.stock.update', $stock);
        $html = $this->asRole('head')->get(route('head.stock.edit', $stock))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();

        $after = (array) DB::table('stocks')->where('id', $stock->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertEquals($before, $after);
    }
}
