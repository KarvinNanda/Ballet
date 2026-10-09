<?php

namespace Tests\Feature\Staff;

use App\Models\Transaction;
use App\Support\TransactionQuota;
use Illuminate\Support\Facades\DB;

class TransactionQuotaTest extends StaffTestCase
{
    private int $student;
    private int $class;

    protected function setUp(): void
    {
        parent::setUp();
        $mapping = DB::table('mapping_class_children')->first();
        $this->student = $mapping->student_id;
        $this->class = $mapping->class_id;
        // Start from a known state: no transactions, zero quota.
        DB::table('transactions')->where('students_id', $this->student)->delete();
        DB::table('mapping_class_children')->where('student_id', $this->student)->update(['quota' => 0]);
        DB::table('students')->where('id', $this->student)->update(['MaxQuota' => 0]);
    }

    private function row(string $status, int $quota, string $date = '2026-01-10'): Transaction
    {
        $id = DB::table('transactions')->insertGetId([
            'students_id' => $this->student, 'class_transactions_id' => $this->class,
            'transaction_date' => $date, 'transaction_payment' => $status === 'Paid' ? $date : null,
            'payment_status' => $status, 'discount' => 0, 'price' => 100, 'desc' => '-',
            'transaction_quota' => $quota, 'created_at' => now(), 'updated_at' => now(),
        ]);

        return Transaction::findOrFail($id);
    }

    private function quota(): int
    {
        return (int) DB::table('mapping_class_children')->where('student_id', $this->student)->where('class_id', $this->class)->value('quota');
    }

    private function maxQuota(): int
    {
        return (int) DB::table('students')->where('id', $this->student)->value('MaxQuota');
    }

    private function pay(Transaction $t): void
    {
        TransactionQuota::track($this->student, $this->class, fn () => $t->forceFill(['payment_status' => 'Paid'])->save());
    }

    public function test_paying_adds_the_quota_once(): void
    {
        $this->pay($this->row('Unpaid', 3));
        $this->assertSame(3, $this->quota());
        $this->assertSame(3, $this->maxQuota());
    }

    public function test_two_payments_in_one_quarter_give_the_sum_not_more(): void // old code gave 9
    {
        $this->pay($this->row('Unpaid', 3));
        $this->pay($this->row('Unpaid', 3));
        $this->assertSame(6, $this->quota());
        $this->assertSame(6, $this->maxQuota());
    }

    public function test_resaving_a_paid_row_adds_nothing(): void
    {
        $t = $this->row('Unpaid', 3);
        $this->pay($t);
        TransactionQuota::track($this->student, $this->class, fn () => $t->forceFill(['desc' => 'edited'])->save());
        $this->assertSame(3, $this->quota());
    }

    public function test_paid_row_from_last_year_is_not_counted_again(): void
    {
        $this->row('Paid', 5, '2025-01-10'); // already paid before; its quota was granted back then
        $this->pay($this->row('Unpaid', 3, '2026-01-10'));
        $this->assertSame(3, $this->quota());
    }

    public function test_changing_the_quota_of_a_paid_row_adds_the_difference(): void
    {
        $t = $this->row('Unpaid', 3);
        $this->pay($t);
        TransactionQuota::track($this->student, $this->class, fn () => $t->forceFill(['transaction_quota' => 4])->save());
        $this->assertSame(4, $this->quota());
        $this->assertSame(4, $this->maxQuota());
    }

    public function test_unpaying_or_deleting_a_paid_row_subtracts(): void
    {
        $a = $this->row('Unpaid', 3);
        $b = $this->row('Unpaid', 4);
        $this->pay($a);
        $this->pay($b);
        TransactionQuota::track($this->student, $this->class, fn () => $a->forceFill(['payment_status' => 'Unpaid'])->save());
        $this->assertSame(4, $this->quota());
        TransactionQuota::track($this->student, $this->class, fn () => $b->delete());
        $this->assertSame(0, $this->quota());
        $this->assertSame(0, $this->maxQuota());
    }

    public function test_legacy_lunas_does_not_count(): void
    {
        $t = $this->row('Unpaid', 3);
        TransactionQuota::track($this->student, $this->class, fn () => $t->forceFill(['payment_status' => 'lunas'])->save());
        $this->assertSame(0, $this->quota());
    }

    public function test_other_classes_keep_their_quota(): void
    {
        $other = DB::table('class_transactions')->where('id', '!=', $this->class)->value('id');
        // updateOrInsert: the student may already be in that class, and (class_id, student_id) becomes unique in Task 6.
        DB::table('mapping_class_children')->updateOrInsert(['class_id' => $other, 'student_id' => $this->student], ['quota' => 7]);
        DB::table('students')->where('id', $this->student)->update(['MaxQuota' => 7]);

        $this->pay($this->row('Unpaid', 3));

        $this->assertSame(7, (int) DB::table('mapping_class_children')->where('student_id', $this->student)->where('class_id', $other)->value('quota'));
        $this->assertSame(10, $this->maxQuota());
    }

    public function test_head_update_to_paid_then_resave_through_http(): void
    {
        $t = $this->row('Unpaid', 3);
        $payload = [
            'inputDisc' => '0', 'inputDesc' => 'x', 'inputStatus' => 'Paid', 'inputJatuhTempo' => '2026-01-10',
            'inputPrice' => 100, 'inputQuota' => 3, 'Type' => 'Transfer', 'inputTanggalBayar' => '2026-01-11',
            'inputSenderName' => '', 'inputBankName' => '',
        ];
        $this->asRole('head')->post(route('head.transaction.update', $t), $payload)->assertRedirect();
        $this->post(route('head.transaction.update', $t), $payload)->assertRedirect();
        $this->assertSame(3, $this->quota());
        $this->assertSame(3, $this->maxQuota());
    }

    public function test_bulk_update_pays_every_sibling_once(): void
    {
        $t = $this->row('Unpaid', 3);
        $this->row('Unpaid', 3);
        $this->row('Unpaid', 3);
        $this->asRole('head')->post(route('head.transaction.update', $t), [
            'inputDisc' => '0', 'inputDesc' => 'x', 'inputStatus' => 'Paid', 'inputJatuhTempo' => '2026-01-10',
            'inputPrice' => 100, 'inputQuota' => 3, 'Type' => 'Transfer', 'inputTanggalBayar' => '2026-01-11',
            'all_transaction' => '1',
        ])->assertRedirect();
        $this->assertSame(9, $this->quota());
    }

    public function test_head_deleting_a_paid_transaction_subtracts(): void
    {
        $t = $this->row('Unpaid', 3);
        $this->pay($t);
        $this->asRole('head')->post(route('head.transaction.destroy', $t))->assertRedirect();
        $this->assertSame(0, $this->quota());
    }

    public function test_head_deleting_an_orphan_transaction_works(): void
    {
        $id = DB::table('transactions')->insertGetId([
            'students_id' => null, 'class_transactions_id' => null, 'transaction_date' => '2026-01-10',
            'payment_status' => 'Paid', 'discount' => 0, 'price' => 100, 'desc' => '-',
            'transaction_quota' => 3, 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->asRole('head')->post(route('head.transaction.destroy', $id))->assertRedirect();
        $this->assertDatabaseMissing('transactions', ['id' => $id]);
    }
}
