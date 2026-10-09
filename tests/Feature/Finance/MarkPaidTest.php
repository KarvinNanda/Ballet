<?php

namespace Tests\Feature\Finance;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarkPaidTest extends TestCase
{
    use RefreshDatabase;

    private function finance(): User
    {
        return User::where('role', 'finance')->firstOrFail();
    }

    /** An Unpaid transaction whose (student, class) has a mapping row, with quota and MaxQuota reset to 0. */
    private function unpaidWithMapping(): Transaction
    {
        $m = DB::table('mapping_class_children')->first();
        DB::table('transactions')->where('students_id', $m->student_id)->delete();
        $id = DB::table('transactions')->insertGetId([
            'students_id' => $m->student_id, 'class_transactions_id' => $m->class_id, 'transaction_date' => '2026-01-10',
            'payment_status' => 'Unpaid', 'discount' => 0, 'price' => 100, 'desc' => '-', 'transaction_quota' => 4,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $t = Transaction::findOrFail($id);
        DB::table('mapping_class_children')->where('student_id', $t->students_id)->where('class_id', $t->class_transactions_id)->update(['quota' => 0]);
        DB::table('students')->where('id', $t->students_id)->update(['MaxQuota' => 0]);

        return $t;
    }

    private function payload(array $override = []): array
    {
        return array_merge(['datePaid' => '2026-01-11', 'inputBankName' => 'BCA', 'inputSenderName' => 'Ibu', 'inputQuota' => 4, 'Type' => 'Transfer'], $override);
    }

    public function test_mark_paid_grants_quota_to_class_and_student(): void
    {
        $t = $this->unpaidWithMapping();
        $this->actingAs($this->finance())->post(route('doPaidTransaction', $t), $this->payload())->assertRedirect();

        $this->assertSame('Paid', $t->fresh()->payment_status);
        $this->assertSame(4, (int) DB::table('mapping_class_children')->where('student_id', $t->students_id)->where('class_id', $t->class_transactions_id)->value('quota'));
        $this->assertSame(4, (int) DB::table('students')->where('id', $t->students_id)->value('MaxQuota'));
    }

    public function test_resubmit_adds_nothing(): void
    {
        $t = $this->unpaidWithMapping();
        $this->actingAs($this->finance())->post(route('doPaidTransaction', $t), $this->payload());
        $this->post(route('doPaidTransaction', $t), $this->payload(['inputQuota' => 12]))->assertSessionHas('error');

        $this->assertSame(4, (int) DB::table('students')->where('id', $t->students_id)->value('MaxQuota'));
        $this->assertSame(4, (int) $t->fresh()->transaction_quota);
    }

    public function test_unknown_transaction_is_404(): void
    {
        $this->actingAs($this->finance())->post(route('doPaidTransaction', 999999), $this->payload())->assertNotFound();
    }

    public function test_quota_and_type_are_bounded(): void
    {
        $t = $this->unpaidWithMapping();
        $this->actingAs($this->finance())->post(route('doPaidTransaction', $t), $this->payload(['inputQuota' => 25, 'Type' => str_repeat('x', 101)]))
            ->assertSessionHasErrors(['inputQuota', 'Type']);
        $this->assertSame('Unpaid', $t->fresh()->payment_status);
    }

    public function test_finance_then_staff_edit_keeps_both_contributions(): void
    {
        $t = $this->unpaidWithMapping();
        $this->actingAs($this->finance())->post(route('doPaidTransaction', $t), $this->payload());

        $sibling = $t->replicate();
        $sibling->forceFill(['payment_status' => 'Unpaid', 'transaction_payment' => null, 'transaction_date' => '2026-02-10'])->save();
        $this->flushSession();
        $this->app['auth']->forgetGuards();
        $this->actingAs(User::where('role', 'head')->firstOrFail())->post(route('head.transaction.update', $sibling), [
            'inputDisc' => '0', 'inputDesc' => 'x', 'inputStatus' => 'Paid', 'inputJatuhTempo' => '2026-02-10',
            'inputPrice' => 100, 'inputQuota' => 3, 'Type' => 'Transfer', 'inputTanggalBayar' => '2026-02-11',
        ])->assertRedirect();

        $this->assertSame(7, (int) DB::table('students')->where('id', $t->students_id)->value('MaxQuota'));
    }

    public function test_five_digit_year_is_a_validation_error_and_changes_nothing(): void
    {
        $t = $this->unpaidWithMapping();
        $rek = DB::table('rekenings')->where('bank_rek', $t->Students->bank_rek)->first();

        $this->actingAs($this->finance())->post(route('doPaidTransaction', $t), $this->payload(['datePaid' => '20266-01-11', 'inputSenderName' => 'Changed']))
            ->assertSessionHasErrors('datePaid');

        $this->assertSame('Unpaid', $t->fresh()->payment_status);
        $this->assertEquals($rek, DB::table('rekenings')->where('bank_rek', $t->Students->bank_rek)->first());
        $this->assertDatabaseMissing('banks', ['bank_name' => 'BCA-new']);
    }
}
