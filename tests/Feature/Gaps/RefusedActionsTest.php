<?php

namespace Tests\Feature\Gaps;

use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

/** A refused request must answer with the refusal AND leave every table it could touch exactly as it was. */
class RefusedActionsTest extends FinanceTestCase
{
    /** Full contents of the tables (rows sorted by content, not every table has an id), for an exact before/after comparison. */
    private function snapshot(string ...$tables): array
    {
        return array_map(
            fn (string $table) => DB::table($table)->get()->map(fn ($row) => json_encode($row))->sort()->values()->all(),
            array_combine($tables, $tables)
        );
    }

    private function updatePayload(array $override = []): array
    {
        return array_merge([
            'inputDisc' => '0', 'inputDesc' => 'changed by admin', 'inputStatus' => 'Unpaid', 'inputJatuhTempo' => '2026-09-01',
            'inputPrice' => 123, 'inputQuota' => 2, 'Type' => 'Cash', 'inputSenderName' => 'Intruder', 'inputBankName' => 'Intruder Bank',
        ], $override);
    }

    public function test_admin_cannot_update_a_paid_transaction(): void
    {
        $t = $this->transaction(['payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);
        $before = $this->snapshot('transactions', 'students', 'rekenings', 'banks');

        $this->asRole('admin')->post(route('admin.transaction.update', $t), $this->updatePayload())->assertForbidden();

        $this->assertEquals($before, $this->snapshot('transactions', 'students', 'rekenings', 'banks'));
    }

    public function test_admin_cannot_delete_a_transaction(): void
    {
        $paid = $this->transaction(['payment_status' => 'Paid', 'transaction_payment' => '2026-09-02', 'student' => 'Paid Kid']);
        $unpaid = $this->transaction(['student' => 'Unpaid Kid']);
        $before = $this->snapshot('transactions', 'students', 'mapping_class_children');

        $this->asRole('admin')->post(route('admin.transaction.destroy', $paid))->assertForbidden();
        $this->post(route('admin.transaction.destroy', $unpaid))->assertForbidden();

        $this->assertEquals($before, $this->snapshot('transactions', 'students', 'mapping_class_children'));
        $this->assertDatabaseHas('transactions', ['id' => $paid->id]);
        $this->assertDatabaseHas('transactions', ['id' => $unpaid->id]);
    }

    public function test_admin_cannot_change_stock(): void
    {
        $stock = $this->stockItem('Admin Hands Off', 7);
        $before = $this->snapshot('stocks', 'report_stocks', 'buyers');

        $this->asRole('admin')
            ->post(route('admin.stock.update', $stock), ['inputName' => 'Renamed', 'inputSize' => 'L', 'inputQty' => 999])->assertForbidden();
        $this->post(route('admin.stock.store'), ['inputName' => 'Brand New', 'inputSize' => 'S', 'inputQty' => 5])->assertForbidden();
        $this->post(route('admin.stock.destroy', $stock))->assertForbidden();

        $this->assertEquals($before, $this->snapshot('stocks', 'report_stocks', 'buyers'));
    }

    public function test_finance_cannot_take_out_more_than_the_stock(): void
    {
        $stock = $this->stockItem('Scarce Item', 3);
        $before = $this->snapshot('stocks', 'report_stocks');

        $this->asRole('finance')->post(route('makeReport', [$stock, 'out']), ['in_out' => 4])
            ->assertRedirect()
            ->assertSessionHas('error')
            ->assertSessionMissing('msg');

        $this->assertEquals($before, $this->snapshot('stocks', 'report_stocks'));
    }

    public function test_a_non_buyer_cannot_post_a_sale(): void
    {
        $stock = $this->stockItem('Not For Finance', 5);
        $before = $this->snapshot('stocks', 'buyers', 'report_stocks');

        $this->asRole('finance')->post(route('buying', $stock), ['name' => 'Sneaky', 'qty' => 2])->assertForbidden();
        $this->asRole('head')->post(route('buying', $stock), ['name' => 'Sneaky', 'qty' => 2])->assertForbidden();

        $this->assertEquals($before, $this->snapshot('stocks', 'buyers', 'report_stocks'));
    }
}
