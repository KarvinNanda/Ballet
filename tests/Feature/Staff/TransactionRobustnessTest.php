<?php

namespace Tests\Feature\Staff;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

/** Staff transaction update/show/edit against legacy data shapes and a double submit. */
class TransactionRobustnessTest extends FinanceTestCase
{
    private function payload(Transaction $t, array $override = []): array
    {
        return array_merge([
            'inputDisc' => '0', 'inputDesc' => 'note', 'inputStatus' => 'Unpaid',
            'inputJatuhTempo' => $t->transaction_date, 'inputPrice' => 450000,
            'inputQuota' => 4, 'Type' => 'Transfer', 'class_id' => $t->class_transactions_id,
            'inputSenderName' => 'New Sender', 'inputBankName' => 'Staff New Bank', 'inputTanggalBayar' => '',
        ], $override);
    }

    /** Flips the row to Paid in the database right after route binding loaded it as Unpaid: a second submit racing the first. */
    private function settleRightAfterBinding(Transaction $t): void
    {
        $done = false;
        Transaction::retrieved(function (Transaction $model) use ($t, &$done) {
            if (! $done && $model->id === $t->id) {
                $done = true;
                DB::table('transactions')->where('id', $t->id)
                    ->update(['payment_status' => 'Paid', 'transaction_payment' => '2026-10-01', 'transaction_type' => 'Cash']);
            }
        });
    }

    public function test_update_leaves_the_empty_number_rekening_alone_for_a_student_with_an_empty_bank_number(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Staff Bank']);
        $t = $this->transaction(['student' => 'Staffempty Kid']);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '']);
        DB::table('rekenings')->insert([
            ['bank_rek' => '', 'nama_pengirim' => 'Nobody', 'banks_id' => $bank],
            ['bank_rek' => '5557771111', 'nama_pengirim' => 'Parent One', 'banks_id' => $bank],
        ]);
        $before = DB::table('rekenings')->orderBy('bank_rek')->get();

        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t))->assertSessionHasNoErrors()->assertRedirect();

        $this->assertEquals(450000, $t->fresh()->price);
        $this->assertEquals($before, DB::table('rekenings')->orderBy('bank_rek')->get());
    }

    public function test_update_still_writes_the_students_own_rekening(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Staff Bank']);
        $t = $this->transaction(['student' => 'Staffown Kid']);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '5557772222']);
        DB::table('rekenings')->insert(['bank_rek' => '5557772222', 'nama_pengirim' => 'Old Sender', 'banks_id' => $bank]);

        $this->asRole('head')->post(route('head.transaction.update', $t), $this->payload($t))->assertSessionHasNoErrors();

        $row = DB::table('rekenings')->where('bank_rek', '5557772222')->first();
        $this->assertSame('New Sender', $row->nama_pengirim);
        $this->assertSame(DB::table('banks')->where('bank_name', 'Staff New Bank')->value('id'), $row->banks_id);
    }

    public function test_pages_and_update_work_when_the_student_row_is_gone(): void
    {
        $t = $this->transaction(['student' => 'Staffgone Kid']);
        DB::table('students')->where('id', $t->students_id)->delete();
        $t = $t->fresh();

        $this->asRole('head')->get(route('head.transaction.show', $t))->assertOk();
        $this->get(route('head.transaction.edit', $t))->assertOk();
        $this->post(route('head.transaction.update', $t), $this->payload($t))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertEquals(450000, $t->fresh()->price);
    }

    public function test_admin_update_is_refused_when_the_row_was_settled_after_the_page_check(): void
    {
        $t = $this->transaction(['student' => 'Staffrace Kid']);
        $maxQuota = DB::table('students')->where('id', $t->students_id)->value('MaxQuota');
        $this->settleRightAfterBinding($t);

        $this->asRole('admin')->post(route('admin.transaction.update', $t), $this->payload($t, ['inputPrice' => 1]))->assertForbidden();

        $row = DB::table('transactions')->where('id', $t->id)->first();
        $this->assertSame('Paid', $row->payment_status);
        $this->assertEquals(350000, $row->price);
        $this->assertSame('Cash', $row->transaction_type);
        $this->assertEquals($maxQuota, DB::table('students')->where('id', $t->students_id)->value('MaxQuota'));
        $this->assertFalse(DB::table('banks')->where('bank_name', 'Staff New Bank')->exists());
    }
}
