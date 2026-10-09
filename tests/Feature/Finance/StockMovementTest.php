<?php

namespace Tests\Feature\Finance;

use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StockMovementTest extends TestCase
{
    use RefreshDatabase;

    private function stock(int $qty = 10): Stock
    {
        $stock = Stock::firstOrFail();
        $stock->forceFill(['quantity' => $qty])->save();

        return $stock;
    }

    private function asFinance(): static
    {
        return $this->actingAs(User::where('role', 'finance')->firstOrFail());
    }

    public function test_in_and_out_change_quantity_and_write_a_report_row(): void
    {
        $stock = $this->stock(10);
        $before = DB::table('report_stocks')->count();
        $this->asFinance()->post(route('makeReport', [$stock, 'in']), ['in_out' => 5])->assertRedirect();
        $this->post(route('makeReport', [$stock, 'out']), ['in_out' => 3])->assertRedirect();
        $this->assertSame(12, (int) $stock->fresh()->quantity);
        $this->assertSame($before + 2, DB::table('report_stocks')->count());
    }

    public function test_out_larger_than_stock_is_refused_and_nothing_is_written(): void
    {
        $stock = $this->stock(2);
        $before = DB::table('report_stocks')->count();
        $this->asFinance()->post(route('makeReport', [$stock, 'out']), ['in_out' => 3])->assertSessionHas('error');
        $this->assertSame(2, (int) $stock->fresh()->quantity);
        $this->assertSame($before, DB::table('report_stocks')->count());
    }

    public function test_bad_amounts_are_validation_errors(): void
    {
        $stock = $this->stock(10);
        foreach (['', '0', '-4', 'abc', ['x'], '100001'] as $bad) {
            $this->asFinance()->post(route('makeReport', [$stock, 'in']), ['in_out' => $bad])->assertSessionHasErrors('in_out');
        }
        $this->assertSame(10, (int) $stock->fresh()->quantity);
    }

    public function test_unknown_type_is_404(): void
    {
        $stock = $this->stock(10);
        $this->asFinance()->post('/finance/stock/report/'.$stock->id.'/quantity', ['in_out' => 1])->assertNotFound();
        $this->assertSame(10, (int) $stock->fresh()->quantity);
    }
}
