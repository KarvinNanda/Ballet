<?php

namespace Tests\Feature\Staff;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionTest extends StaffTestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function unpaid(): Transaction
    {
        return Transaction::where('payment_status', 'Unpaid')->firstOrFail();
    }

    private function payload(Transaction $t, array $override = []): array
    {
        return array_merge([
            'inputDisc' => '0', 'inputDesc' => 'note', 'inputStatus' => 'Unpaid',
            'inputJatuhTempo' => $t->transaction_date, 'inputPrice' => 450000,
            'inputQuota' => 4, 'Type' => $t->transaction_type, 'class_id' => $t->class_transactions_id,
            'inputSenderName' => 'Sender', 'inputBankName' => '', 'inputTanggalBayar' => '',
        ], $override);
    }

    public function test_admin_edit_keeps_the_price(): void // was NULL
    {
        $t = $this->unpaid();
        $this->asRole('admin')->post(route('admin.transaction.update', $t), $this->payload($t))->assertRedirect();
        $this->assertEquals(450000, $t->fresh()->price);
    }

    public function test_sender_without_bank_name_does_not_500(): void // $banks undefined
    {
        $t = $this->unpaid();
        $before = DB::table('rekenings')->where('bank_rek', $t->Students->bank_rek)->value('banks_id');
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t))->assertRedirect();
        $this->assertEquals($before, DB::table('rekenings')->where('bank_rek', $t->Students->bank_rek)->value('banks_id'));
    }

    public function test_empty_payment_date_keeps_the_old_one(): void
    {
        $t = Transaction::whereNotNull('transaction_payment')->firstOrFail();
        $old = $t->transaction_payment;
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['inputStatus' => 'Paid', 'inputTanggalBayar' => $old]));
        $this->post(route('head.transaction.update', $t), $this->payload($t, ['inputStatus' => 'Unpaid', 'inputTanggalBayar' => '']));
        $this->assertSame($old, $t->fresh()->transaction_payment);
    }

    public function test_paid_without_a_payment_date_is_refused(): void
    {
        $t = $this->unpaid();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['inputStatus' => 'Paid']))
            ->assertSessionHas('error');
        $this->assertSame('Unpaid', $t->fresh()->payment_status);
    }

    public function test_admin_cannot_edit_a_paid_transaction(): void
    {
        $t = Transaction::where('payment_status', 'Paid')->firstOrFail();
        $this->asRole('admin')->get(route('admin.transaction.edit', $t))->assertForbidden();
        $this->post(route('admin.transaction.update', $t), $this->payload($t, ['inputPrice' => 1]))->assertForbidden();
        $this->assertNotEquals(1, $t->fresh()->price);
    }

    public function test_admin_cannot_delete(): void
    {
        $t = $this->unpaid();
        $this->asRole('admin')->post(route('admin.transaction.destroy', $t))->assertForbidden();
        $this->assertNotNull($t->fresh());
        $this->asRole('head')->post(route('head.transaction.destroy', $t))->assertRedirect();
        $this->assertNull($t->fresh());
    }

    public function test_quota_input_is_bounded(): void
    {
        $t = $this->unpaid();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['inputQuota' => 25]))
            ->assertSessionHasErrors('inputQuota');
    }

    public function test_tampered_class_id_does_not_touch_another_class(): void
    {
        $t = $this->unpaid();
        $otherClass = DB::table('class_transactions')->where('id', '!=', $t->class_transactions_id)->value('id');
        $otherId = DB::table('transactions')->insertGetId([
            'students_id' => $t->students_id, 'class_transactions_id' => $otherClass,
            'transaction_date' => $t->transaction_date, 'transaction_payment' => null,
            'payment_status' => 'Unpaid', 'discount' => 0, 'price' => 111, 'desc' => '-',
            'transaction_quota' => 8, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, [
            'all_transaction' => '1', 'inputStatus' => 'Paid', 'inputTanggalBayar' => $t->transaction_date,
            'class_id' => $otherClass,
        ]))->assertRedirect();

        $other = DB::table('transactions')->where('id', $otherId)->first();
        $this->assertSame('Unpaid', $other->payment_status);
        $this->assertEquals(111, $other->price);
        $this->assertSame('Paid', $t->fresh()->payment_status);
        $this->assertEquals(450000, $t->fresh()->price);
    }

    public function test_edit_detail_and_old_search_redirect_render_for_both_roles(): void
    {
        $t = $this->unpaid();
        foreach (['admin', 'head'] as $role) {
            $html = $this->asRole($role)->get(route("{$role}.transaction.edit", $t))->assertOk()->getContent();
            $this->assertStringContainsString('name="inputPrice" value="'.$t->price.'"', $html);
            $this->assertStringNotContainsString('inputBankAccount', $html);
            $this->get(route("{$role}.transaction.show", $t))->assertOk();
        }
        $this->asRole('head')->get('/head/transaction/search')->assertRedirect('/head/transaction')->assertStatus(301);
    }

    /** @return array{0: Transaction, 1: int} unpaid row and the id of a Paid sibling (same student and class) */
    private function withPaidSibling(): array
    {
        $t = $this->unpaid();
        $siblingId = DB::table('transactions')->insertGetId([
            'students_id' => $t->students_id, 'class_transactions_id' => $t->class_transactions_id,
            'transaction_date' => $t->transaction_date, 'transaction_payment' => '2026-01-05',
            'payment_status' => 'Paid', 'discount' => 0, 'price' => 111, 'desc' => '-',
            'transaction_quota' => 8, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return [$t, $siblingId];
    }

    private function bulkPayload(Transaction $t): array
    {
        return $this->payload($t, ['all_transaction' => '1', 'inputStatus' => 'Paid', 'inputTanggalBayar' => '2026-02-02']);
    }

    public function test_admin_bulk_update_leaves_paid_siblings_alone(): void
    {
        [$t, $siblingId] = $this->withPaidSibling();
        $this->asRole('admin')->post(route('admin.transaction.update', $t), $this->bulkPayload($t))->assertRedirect();

        $sibling = DB::table('transactions')->where('id', $siblingId)->first();
        $this->assertEquals(111, $sibling->price);
        $this->assertSame('2026-01-05', substr($sibling->transaction_payment, 0, 10));
        $this->assertSame('Paid', $t->fresh()->payment_status);
        $this->assertEquals(450000, $t->fresh()->price);
    }

    public function test_head_bulk_update_does_rewrite_paid_siblings(): void
    {
        [$t, $siblingId] = $this->withPaidSibling();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->bulkPayload($t))->assertRedirect();

        $sibling = DB::table('transactions')->where('id', $siblingId)->first();
        $this->assertEquals(450000, $sibling->price);
        $this->assertSame('2026-02-02', substr($sibling->transaction_payment, 0, 10));
    }

    public function test_long_description_is_a_validation_error_not_a_500(): void
    {
        $t = $this->unpaid();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['inputDesc' => str_repeat('x', 300)]))
            ->assertSessionHasErrors('inputDesc');
    }

    public function test_admin_bulk_update_leaves_lunas_siblings_alone(): void
    {
        [$t, $siblingId] = $this->withPaidSibling();
        DB::table('transactions')->where('id', $siblingId)->update(['payment_status' => 'lunas']);
        $this->asRole('admin')->post(route('admin.transaction.update', $t), $this->bulkPayload($t))->assertRedirect();

        $sibling = DB::table('transactions')->where('id', $siblingId)->first();
        $this->assertSame('lunas', $sibling->payment_status);
        $this->assertEquals(111, $sibling->price);
        $this->assertSame('2026-01-05', substr($sibling->transaction_payment, 0, 10));
        $this->assertSame('Paid', $t->fresh()->payment_status);
    }

    private function storePayload(array $override = []): array
    {
        return array_merge([
            'nis' => DB::table('students')->value('id'),
            'class' => DB::table('class_transactions')->value('id'),
            'dateTime' => '2026-01-10', 'Price' => 450000,
        ], $override);
    }

    public function test_store_rejects_unknown_student_and_class_ids(): void
    {
        $before = DB::table('transactions')->count();
        $this->asRole('head')->post(route('head.transaction.store'), $this->storePayload(['nis' => 999999]))
            ->assertSessionHasErrors('nis');
        $this->post(route('head.transaction.store'), $this->storePayload(['class' => 999999]))
            ->assertSessionHasErrors('class');
        $this->post(route('head.transaction.store'), $this->storePayload(['nis' => 'abc']))
            ->assertSessionHasErrors('nis');
        $this->assertSame($before, DB::table('transactions')->count());

        $this->post(route('head.transaction.store'), $this->storePayload())->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame($before + 1, DB::table('transactions')->count());
    }

    public function test_long_bank_name_is_a_validation_error(): void
    {
        $t = $this->unpaid();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['inputBankName' => str_repeat('x', 300)]))
            ->assertSessionHasErrors('inputBankName');
        $this->assertSame(0, DB::table('banks')->where('bank_name', str_repeat('x', 300))->count());
    }

    public function test_type_longer_than_its_column_is_a_validation_error(): void // column is VARCHAR(100)
    {
        $t = $this->unpaid();
        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t, ['Type' => str_repeat('x', 101)]))
            ->assertSessionHasErrors('Type');
    }

    public function test_admin_gets_403_not_422_on_a_paid_row_with_bad_input(): void
    {
        $t = Transaction::where('payment_status', 'Paid')->firstOrFail();
        $this->asRole('admin')->post(route('admin.transaction.update', $t), ['inputQuota' => 'abc'])->assertForbidden();
    }
}
