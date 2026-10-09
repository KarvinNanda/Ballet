<?php

namespace Tests\Feature\Gaps;

use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Finance\FinanceTestCase;

/** The last value a rule accepts and the first one it refuses. */
class BoundariesTest extends FinanceTestCase
{
    public function test_a_buyer_can_sell_the_whole_stock_and_the_item_then_shows_sold_out(): void
    {
        $stock = $this->stockItem('Last Pair Pointe', 4);
        $reports = DB::table('report_stocks')->count();

        $this->asBuyer()->post(route('buying', $stock), ['name' => 'Ibu Rina', 'qty' => 4])
            ->assertRedirect()->assertSessionHasNoErrors()->assertSessionHas('msg', 'Thank You');

        $this->assertSame(0, (int) $stock->fresh()->quantity);
        $this->assertSame($reports + 1, DB::table('report_stocks')->count());
        $this->assertDatabaseHas('buyers', ['stock_id' => $stock->id, 'qty' => 4]);

        $html = $this->get(route('buyer', ['search' => 'Last Pair']))->assertOk()->getContent();
        $row = $this->rowFor($html, 'Last Pair Pointe');
        $this->assertStringContainsString('Sold out', $row);
        $this->assertStringNotContainsString(route('buyingItem', $stock->id), $row);
        $this->assertStringNotContainsString('>Sell<', $row);
    }

    public function test_one_more_than_the_stock_is_refused_on_the_qty_field(): void
    {
        $stock = $this->stockItem('Short Item', 4);

        $this->asBuyer()->post(route('buying', $stock), ['name' => 'Ibu Rina', 'qty' => 5])->assertSessionHasErrors('qty');

        $this->assertSame(4, (int) $stock->fresh()->quantity);
    }

    private function markPaidPayload(int $quota): array
    {
        return ['datePaid' => '2026-09-02', 'inputBankName' => 'BCA', 'inputSenderName' => 'Ibu', 'inputQuota' => $quota, 'Type' => 'Transfer'];
    }

    public function test_finance_mark_paid_accepts_quota_24_and_refuses_25(): void
    {
        $t = $this->transaction();

        $this->asRole('finance')->post(route('doPaidTransaction', $t), $this->markPaidPayload(25))->assertSessionHasErrors('inputQuota');
        $this->assertSame('Unpaid', $t->fresh()->payment_status);
        $this->assertSame(4, (int) $t->fresh()->transaction_quota);

        $this->post(route('doPaidTransaction', $t), $this->markPaidPayload(24))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Paid', $t->fresh()->payment_status);
        $this->assertSame(24, (int) $t->fresh()->transaction_quota);
    }

    private function updatePayload(int $quota): array
    {
        return [
            'inputDisc' => '0', 'inputDesc' => 'x', 'inputStatus' => 'Unpaid', 'inputJatuhTempo' => '2026-09-01',
            'inputPrice' => 350000, 'inputQuota' => $quota, 'Type' => 'Transfer',
        ];
    }

    public function test_head_transaction_update_accepts_quota_24_and_refuses_25(): void
    {
        $t = $this->transaction();

        $this->asRole('head')->post(route('head.transaction.update', $t), $this->updatePayload(25))->assertSessionHasErrors('inputQuota');
        $this->assertSame(4, (int) $t->fresh()->transaction_quota);

        $this->post(route('head.transaction.update', $t), $this->updatePayload(24))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(24, (int) $t->fresh()->transaction_quota);
    }

    /** Every field the detail form posts; values are the student's current ones unless overridden. */
    private function studentPayload(Student $s, array $override = []): array
    {
        $rek = DB::table('rekenings')
            ->leftJoin('banks', 'banks.id', 'rekenings.banks_id')
            ->where('rekenings.bank_rek', $s->bank_rek)
            ->first(['rekenings.nama_pengirim', 'banks.bank_name']);

        return array_merge([
            'nis' => $s->nis, 'LongName' => $s->LongName, 'ShortName' => $s->ShortName,
            'Email' => $s->Email, 'dob' => $s->Dob, 'Address' => $s->Address,
            'nama_orang_tua' => $s->nama_orang_tua, 'city' => $s->City, 'kode_pos' => $s->kode_pos,
            'Phone1' => $s->Phone1, 'Phone2' => $s->Phone2, 'Whatsapp' => $s->Whatsapp,
            'Instagram' => $s->Instagram, 'Line' => $s->Line, 'EnrollDate' => $s->EnrollDate,
            'Quota' => $s->Quota ?? 0, 'Quota_original' => $s->Quota ?? 0, 'MaxQuota' => 12, 'is_new' => 'No', 'status' => $s->Status,
            'bank' => $rek->bank_name ?? 'BCA', 'accountno' => $s->bank_rek ?? '1234567890',
            'sender' => $rek->nama_pengirim ?? 'Parent',
        ], $override);
    }

    public function test_student_instagram_accepts_255_characters_and_refuses_256(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        $old = $s->Instagram;

        $this->asRole('head')->post(route('head.student.update', $s), $this->studentPayload($s, ['Instagram' => str_repeat('i', 256)]))
            ->assertSessionHasErrors('Instagram');
        $this->assertSame($old, $s->fresh()->Instagram);

        $this->post(route('head.student.update', $s), $this->studentPayload($s, ['Instagram' => str_repeat('i', 255)]))
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(str_repeat('i', 255), $s->fresh()->Instagram);
    }
}
