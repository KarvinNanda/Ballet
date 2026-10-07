<?php

namespace Tests\Feature\Staff;

use App\Models\Stock;

class StockTest extends StaffTestCase
{
    public function test_both_roles_see_the_list(): void
    {
        $this->asRole('admin')->get(route('admin.stock.index'))->assertOk();
        $this->asRole('head')->get(route('head.stock.index'))->assertOk();
    }

    public function test_only_head_sees_manage_buttons(): void
    {
        $this->asRole('admin')->get(route('admin.stock.index'))->assertDontSee(route('admin.stock.create'));
        $this->asRole('head')->get(route('head.stock.index'))->assertSee(route('head.stock.create'));
    }

    public function test_admin_cannot_add_update_or_delete(): void
    {
        $stock = Stock::firstOrFail();
        $count = Stock::count();

        $this->asRole('admin')->get(route('admin.stock.create'))->assertForbidden();
        $this->post(route('admin.stock.store'), ['inputName' => 'X', 'inputSize' => 'M', 'inputQty' => 1])->assertForbidden();
        $this->get(route('admin.stock.edit', $stock))->assertForbidden();
        $this->post(route('admin.stock.update', $stock), ['inputName' => 'Changed', 'inputSize' => 'M', 'inputQty' => 1])->assertForbidden();
        $this->post(route('admin.stock.destroy', $stock))->assertForbidden();

        $this->assertSame($count, Stock::count());
        $this->assertSame($stock->name, $stock->fresh()->name);
    }

    public function test_head_can_add_and_update(): void
    {
        $this->asRole('head')->post(route('head.stock.store'), ['inputName' => 'Leotard', 'inputSize' => 'M', 'inputQty' => 5])
            ->assertRedirect(route('head.stock.index'));
        $stock = Stock::where('name', 'Leotard')->firstOrFail();

        $this->get(route('head.stock.edit', $stock))->assertOk();
        $this->post(route('head.stock.update', $stock), ['inputName' => 'Tutu', 'inputSize' => 'S', 'inputQty' => 7])->assertRedirect();
        $this->assertSame('Tutu', $stock->fresh()->name);
        $this->assertSame(7, (int) $stock->fresh()->quantity);
    }

    public function test_head_can_delete(): void
    {
        $stock = Stock::firstOrFail();
        $this->asRole('head')->post(route('head.stock.destroy', $stock))->assertRedirect();
        $this->assertNull($stock->fresh());
    }

    public function test_sort_rejects_unknown_columns(): void
    {
        $this->asRole('admin')->get(route('admin.stock.sort', ['column' => 'password', 'direction' => 'asc']))->assertNotFound();
    }

    public function test_get_search_form_does_not_put_the_csrf_token_in_the_url(): void
    {
        $html = $this->asRole('head')->get(route('head.stock.index'))->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/<form[^>]*method="get"[^>]*>(?:(?!<\/form>).)*name="search"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<form[^>]*method="get"[^>]*>(?:(?!<\/form>).)*name="_token"/s', $html);
    }
}
