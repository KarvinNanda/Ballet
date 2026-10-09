<?php

namespace Tests\Feature\Finance;

use App\Models\User;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class FinanceTransactionPagesTest extends FinanceTestCase
{
    private function listHtml(array $query = []): string
    {
        return $this->asRole('finance')->get(route('financeTransaction', $query))->assertOk()->getContent();
    }

    public function test_list_defaults_to_unpaid_and_the_status_segment_switches(): void
    {
        $this->transaction(['student' => 'Finseg Unpaid']);
        $this->transaction(['student' => 'Finseg Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $html = $this->listHtml(['search' => 'Finseg']);
        $this->assertStringContainsString('<h1 class="page-title">Transactions</h1>', $html);
        $this->assertStringContainsString('Finseg Unpaid', $html);
        $this->assertStringNotContainsString('Finseg Paid', $html);
        $this->assertStringContainsString('aria-current="page">Unpaid</a>', $html);
        $this->assertStringContainsString('<td class="d-none d-lg-table-cell">Waiting</td>', $this->rowFor($html, 'Finseg Unpaid'));

        $html = $this->listHtml(['search' => 'Finseg', 'status' => 'Paid']);
        $this->assertStringNotContainsString('Finseg Unpaid', $html);
        $this->assertStringContainsString('aria-current="page">Paid</a>', $html);
        $this->assertStringContainsString('<td class="d-none d-lg-table-cell">02 Sep 2026</td>', $this->rowFor($html, 'Finseg Paid'));

        $html = $this->listHtml(['search' => 'Finseg', 'status' => 'all']);
        $this->assertStringContainsString('Finseg Unpaid', $html);
        $this->assertStringContainsString('Finseg Paid', $html);
        $this->assertStringContainsString('aria-current="page">All</a>', $html);

        // The segment links keep the search; Unpaid is the default, so its link carries no status.
        $this->assertStringContainsString('href="'.e(route('financeTransaction', ['search' => 'Finseg'])).'"', $html);
        $this->assertStringContainsString('href="'.e(route('financeTransaction', ['search' => 'Finseg', 'status' => 'Paid'])).'"', $html);
    }

    public function test_unknown_status_falls_back_to_unpaid(): void
    {
        $this->transaction(['student' => 'Finjunk Unpaid']);
        $this->transaction(['student' => 'Finjunk Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $urls = [
            route('financeTransaction', ['search' => 'Finjunk', 'status' => 'lunas']),
            route('financeTransactionSorting', ['column' => 'price', 'search' => 'Finjunk', 'status' => 'lunas']),
        ];
        foreach ($urls as $url) {
            $this->asRole('finance')->get($url)->assertOk()->assertSee('Finjunk Unpaid')->assertDontSee('Finjunk Paid');
        }
    }

    public function test_list_and_mark_paid_page_use_the_transaction_price_not_the_course_price(): void
    {
        $this->firstClassWithOtherPrices(); // course 999,000, frozen class price 888,000
        $t = $this->transaction(['student' => 'Finprice Kid', 'price' => 350000]);

        $row = $this->rowFor($this->listHtml(['search' => 'Finprice']), 'Finprice Kid');
        $this->assertStringContainsString('Rp350,000', $row);
        $this->assertStringNotContainsString('999,000', $row);
        $this->assertStringNotContainsString('888,000', $row);
        $this->assertStringNotContainsString('row-note', $row, 'no discount, no breakdown line');

        $html = $this->asRole('finance')->get(route('paidTransaction', $t))->assertOk()->getContent();
        $this->assertStringContainsString('<dt>Price</dt><dd>Rp350,000</dd>', $html);
        $this->assertMatchesRegularExpression('/<dt>Total<\/dt>\s*<dd>\s*Rp350,000\s*<\/dd>/', $html);
        $this->assertStringNotContainsString('999,000', $html);
        $this->assertStringNotContainsString('888,000', $html);
    }

    public function test_list_shows_percent_amount_and_invalid_discounts_without_crashing(): void
    {
        $this->transaction(['student' => 'Findisc Percent', 'discount' => '10%']);
        $this->transaction(['student' => 'Findisc Amount', 'discount' => '50000']);
        $this->transaction(['student' => 'Findisc Invalid', 'discount' => 'abc']);
        $this->transaction(['student' => 'Findisc None', 'discount' => '0']);

        $html = $this->listHtml(['search' => 'Findisc']);

        $percent = $this->rowFor($html, 'Findisc Percent');
        $this->assertStringContainsString('Rp315,000', $percent);
        $this->assertStringContainsString('<div class="row-note">Rp350,000 − 10%</div>', $percent);

        $amount = $this->rowFor($html, 'Findisc Amount');
        $this->assertStringContainsString('Rp300,000', $amount);
        $this->assertStringContainsString('<div class="row-note">Rp350,000 − Rp50,000</div>', $amount);

        $invalid = $this->rowFor($html, 'Findisc Invalid');
        $this->assertStringContainsString('Rp350,000', $invalid);
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Invalid discount</span>', $invalid);

        $none = $this->rowFor($html, 'Findisc None');
        $this->assertStringContainsString('Rp350,000', $none);
        $this->assertStringNotContainsString('row-note', $none);
    }

    public function test_list_shows_each_transaction_once_including_one_without_a_class(): void
    {
        $classId = (int) DB::table('class_transactions')->orderBy('id')->value('id');
        // A second teacher on the class: a teacher join would list its transaction twice.
        DB::table('mapping_class_teachers')->insert([
            'class_id' => $classId, 'user_id' => User::factory()->create(['role' => 'teacher'])->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->transaction(['student' => 'Fincount Two Teachers', 'class_transactions_id' => $classId]);
        $this->transaction(['student' => 'Fincount No Class', 'class_transactions_id' => null]);
        $this->transaction(['student' => 'Fincount Inactive', 'student_status' => 'non-aktif']);
        $this->transaction(['student' => 'Fincount Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $response = $this->asRole('finance')->get(route('financeTransaction', ['search' => 'Fincount', 'status' => 'all']))->assertOk();
        $page = $response->viewData('transactions');
        $html = $response->getContent();

        $this->assertSame(4, $page->total());
        $this->assertSame(20, $page->perPage());
        foreach (['Fincount Two Teachers', 'Fincount No Class', 'Fincount Inactive', 'Fincount Paid'] as $name) {
            $this->assertSame(1, substr_count($html, $name), $name);
        }
        $this->assertStringContainsString('<td>-</td>', $this->rowFor($html, 'Fincount No Class'));
    }

    public function test_sort_and_search_routes_share_the_filter_and_links_carry_it(): void
    {
        $this->transaction(['student' => 'Finsort Cheap', 'price' => 100000, 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);
        $this->transaction(['student' => 'Finsort Dear', 'price' => 500000, 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);
        $this->transaction(['student' => 'Finsort Unpaid', 'price' => 50000]);
        $this->transaction(['student' => 'Finother Kid', 'price' => 1000, 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $html = $this->asRole('finance')->get(route('financeTransactionSorting', ['column' => 'price', 'search' => 'Finsort', 'status' => 'Paid']))
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('Finsort Unpaid', $html);
        $this->assertStringNotContainsString('Finother Kid', $html);
        $this->assertLessThan(strpos($html, 'Finsort Dear'), strpos($html, 'Finsort Cheap'), 'price ascending');

        $this->asRole('finance')->get(route('searchTransaction', ['search' => 'Finsort', 'status' => 'Paid']))
            ->assertOk()->assertSee('Finsort Dear')->assertDontSee('Finsort Unpaid')->assertDontSee('Finother Kid');

        $this->asRole('finance')->get(route('financeTransaction', ['search' => 'Finsort', 'status' => 'Paid']))
            ->assertSee(route('financeTransactionSorting', ['column' => 'price', 'search' => 'Finsort', 'status' => 'Paid']))
            ->assertSee(route('financeTransactionSorting', ['column' => 'payment_status', 'search' => 'Finsort', 'status' => 'Paid']));
    }

    public function test_only_unpaid_rows_offer_record_payment(): void
    {
        $unpaid = $this->transaction(['student' => 'Finact Unpaid']);
        $paid = $this->transaction(['student' => 'Finact Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $html = $this->listHtml(['search' => 'Finact', 'status' => 'all']);

        $unpaidRow = $this->rowFor($html, 'Finact Unpaid');
        $this->assertStringContainsString('<a href="'.route('paidTransaction', $unpaid).'" class="btn btn-sm btn-primary">Record payment</a>', $unpaidRow);
        $this->assertStringContainsString('<span class="status-badge status-badge-warning">Unpaid</span>', $unpaidRow);

        $paidRow = $this->rowFor($html, 'Finact Paid');
        $this->assertStringNotContainsString(route('paidTransaction', $paid), $paidRow);
        $this->assertStringNotContainsString('Record payment', $paidRow);
        $this->assertStringContainsString('<span class="status-badge status-badge-success">Paid</span>', $paidRow);
    }

    public function test_empty_result_shows_the_empty_state(): void
    {
        $this->asRole('finance')->get(route('financeTransaction', ['search' => 'zzz-no-such-kid']))
            ->assertOk()->assertSeeText('No transactions found')->assertDontSee('<table', false);
    }

    public function test_mark_paid_page_shows_the_total_for_each_discount_kind(): void
    {
        $cases = [
            '10%' => ['10%', 'Rp315,000'],
            '50000' => ['Rp50,000', 'Rp300,000'],
            'abc' => ['Invalid discount', 'Rp350,000'],
        ];

        foreach ($cases as $discount => [$label, $total]) {
            $discount = (string) $discount; // PHP turns the key '50000' into an int
            $t = $this->transaction(['student' => 'Finpay Kid', 'discount' => $discount]);
            $html = $this->asRole('finance')->get(route('paidTransaction', $t))->assertOk()->getContent();

            $this->assertStringContainsString('<h1 class="page-title">Record payment · Finpay Kid</h1>', $html, $discount);
            $this->assertStringContainsString('<dt>Discount</dt><dd>'.$label.'</dd>', $html, $discount);
            $after = $discount === 'abc' ? '<span class="status-badge status-badge-warning">Invalid discount<\/span>' : '<\/dd>';
            $this->assertMatchesRegularExpression('/<dt>Total<\/dt>\s*<dd>\s*'.preg_quote($total, '/').'\s*'.$after.'/', $html, $discount);
            $this->assertStringContainsString('data-confirm="Mark '.$total.' from Finpay Kid as paid?"', $html, $discount);
        }
    }

    public function test_mark_paid_form_prefills_the_account_and_keeps_the_field_names(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Fin Bank']);
        $t = $this->transaction(['student' => 'Finform Kid', 'transaction_quota' => 4]);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '5553334444']);
        DB::table('rekenings')->insert(['bank_rek' => '5553334444', 'nama_pengirim' => 'Fin Parent', 'banks_id' => $bank]);
        $listUrl = route('financeTransaction', ['search' => 'Finform']);

        $html = $this->asRole('finance')->from($listUrl)->get(route('paidTransaction', $t))->assertOk()->getContent();

        $this->assertStringContainsString('<form method="POST" action="'.route('doPaidTransaction', $t).'" data-confirm="Mark Rp350,000 from Finform Kid as paid?" class="card">', $html);
        $this->assertStringContainsString('<input type="hidden" name="return_url" value="'.e($listUrl).'">', $html);
        $this->assertStringContainsString('<input type="date" id="field-datePaid" name="datePaid" value="" class="form-control" required', $html);
        $this->assertStringContainsString('<input type="text" id="field-inputBankName" name="inputBankName" value="Fin Bank" class="form-control" required', $html);
        $this->assertStringContainsString('<input type="text" id="field-inputSenderName" name="inputSenderName" value="Fin Parent" class="form-control" required', $html);
        $this->assertStringContainsString('<input type="number" id="field-inputQuota" name="inputQuota" value="4" class="form-control" min="1" max="24" required', $html);
        $this->assertStringContainsString('<input type="text" id="field-Type" name="Type" value="" class="form-control" required', $html);
        $this->assertStringContainsString('<dt>Account number</dt><dd>5553334444</dd>', $html);
        $course = DB::table('class_transactions')->join('class_types', 'class_types.id', '=', 'class_transactions.class_type_id')
            ->orderBy('class_transactions.id')->value('class_types.class_name');
        $this->assertNotEmpty($course);
        $this->assertStringContainsString('<dt>Class</dt><dd>'.e($course).'</dd>', $html);
        $this->assertStringContainsString('Mark as paid', $html);
    }

    public function test_posting_the_rendered_mark_paid_form_marks_it_paid(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Fin Bank']);
        $t = $this->transaction(['student' => 'Finpost Kid', 'transaction_quota' => 4]);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '5553335555']);
        DB::table('rekenings')->insert(['bank_rek' => '5553335555', 'nama_pengirim' => 'Fin Parent', 'banks_id' => $bank]);
        $listUrl = route('financeTransaction', ['search' => 'Finpost']);
        $action = route('doPaidTransaction', $t);

        $html = $this->asRole('finance')->from($listUrl)->get(route('paidTransaction', $t))->assertOk()->getContent();
        $fields = array_merge($this->formFields($html, $action), ['datePaid' => '2026-10-09', 'Type' => 'Transfer']);

        $this->post($action, $fields)->assertSessionHasNoErrors()->assertRedirect($listUrl);

        $row = DB::table('transactions')->where('id', $t->id)->first();
        $this->assertSame('Paid', $row->payment_status);
        $this->assertSame('2026-10-09', $row->transaction_payment);
        $this->assertSame(4, (int) $row->transaction_quota);
        $this->assertSame('Transfer', $row->transaction_type);
        $this->assertSame('Fin Parent', DB::table('rekenings')->where('bank_rek', '5553335555')->value('nama_pengirim'));
    }

    public function test_a_settled_transaction_shows_no_form_and_is_still_refused(): void
    {
        $paid = $this->transaction(['student' => 'Finsettled Kid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $html = $this->asRole('finance')->get(route('paidTransaction', $paid))->assertOk()->getContent();
        $this->assertStringContainsString('This transaction is already settled.', $html);
        $this->assertStringNotContainsString('action="'.route('doPaidTransaction', $paid).'"', $html);

        $this->post(route('doPaidTransaction', $paid), [
            'datePaid' => '2026-10-09', 'inputBankName' => 'BCA', 'inputSenderName' => 'Ibu', 'inputQuota' => 4, 'Type' => 'Transfer',
        ])->assertSessionHas('error', 'This transaction is already settled.');
        $this->assertSame('2026-09-02', DB::table('transactions')->where('id', $paid->id)->value('transaction_payment'));
    }

    public function test_mark_paid_page_renders_for_a_student_without_a_bank_account(): void
    {
        $t = $this->transaction(['student' => 'Finnobank Kid']); // factory students have no bank_rek

        $html = $this->asRole('finance')->get(route('paidTransaction', $t))->assertOk()->getContent();

        $this->assertStringContainsString('<dt>Account number</dt><dd>-</dd>', $html);
        $this->assertStringContainsString('<input type="text" id="field-inputBankName" name="inputBankName" value="" class="form-control" required', $html);
    }

    public function test_mark_paid_page_shows_no_class_for_a_transaction_without_one(): void
    {
        $t = $this->transaction(['student' => 'Finnoclass Kid', 'class_transactions_id' => null]);

        $this->asRole('finance')->get(route('paidTransaction', $t))->assertOk()
            ->assertSee('<dt>Class</dt><dd>No class</dd>', false);
    }

    /** @return array<string, string> the rekening rows by number => sender, as stored now */
    private function rekeningSenders(): array
    {
        return DB::table('rekenings')->pluck('nama_pengirim', 'bank_rek')->all();
    }

    private function payload(): array
    {
        return ['datePaid' => '2026-10-09', 'inputBankName' => 'Fin New Bank', 'inputSenderName' => 'New Sender', 'inputQuota' => 4, 'Type' => 'Transfer'];
    }

    public function test_marking_paid_updates_only_the_rekening_of_the_students_own_number(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Fin Bank']);
        $t = $this->transaction(['student' => 'Finrek Kid']);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '5554441111']);
        DB::table('rekenings')->insert([
            ['bank_rek' => '5554441111', 'nama_pengirim' => 'Own Parent', 'banks_id' => $bank],
            ['bank_rek' => '5554442222', 'nama_pengirim' => 'Other Parent', 'banks_id' => $bank],
        ]);

        $this->asRole('finance')->post(route('doPaidTransaction', $t), $this->payload())->assertSessionHasNoErrors();

        $newBank = DB::table('banks')->where('bank_name', 'Fin New Bank')->value('id');
        $this->assertNotNull($newBank);
        $this->assertSame('Paid', DB::table('transactions')->where('id', $t->id)->value('payment_status'));
        $own = DB::table('rekenings')->where('bank_rek', '5554441111')->first();
        $this->assertSame('New Sender', $own->nama_pengirim);
        $this->assertSame($newBank, (int) $own->banks_id);
        $other = DB::table('rekenings')->where('bank_rek', '5554442222')->first();
        $this->assertSame('Other Parent', $other->nama_pengirim);
        $this->assertSame($bank, (int) $other->banks_id);
    }

    public function test_marking_paid_leaves_every_rekening_alone_for_a_student_without_a_bank_number(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Fin Bank']);
        $t = $this->transaction(['student' => 'Finnull Kid']); // factory students have bank_rek NULL
        $this->assertNull(DB::table('students')->where('id', $t->students_id)->value('bank_rek'));
        DB::table('rekenings')->insert([
            ['bank_rek' => '5555551111', 'nama_pengirim' => 'Parent One', 'banks_id' => $bank],
            ['bank_rek' => '5555552222', 'nama_pengirim' => 'Parent Two', 'banks_id' => $bank],
        ]);
        $before = DB::table('rekenings')->orderBy('bank_rek')->get();

        $this->asRole('finance')->post(route('doPaidTransaction', $t), $this->payload())->assertSessionHasNoErrors();

        $this->assertSame('Paid', DB::table('transactions')->where('id', $t->id)->value('payment_status'));
        $this->assertEquals($before, DB::table('rekenings')->orderBy('bank_rek')->get());
    }

    public function test_marking_paid_leaves_the_empty_number_rekening_alone_for_a_student_with_an_empty_bank_number(): void
    {
        // rekenings.bank_rek is NOT NULL, so the reachable "no number" shape is an empty string, which the unique index allows once.
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Fin Bank']);
        $t = $this->transaction(['student' => 'Finempty Kid']);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '']);
        DB::table('rekenings')->insert([
            ['bank_rek' => '', 'nama_pengirim' => 'Nobody', 'banks_id' => $bank],
            ['bank_rek' => '5556661111', 'nama_pengirim' => 'Parent One', 'banks_id' => $bank],
        ]);
        $before = DB::table('rekenings')->orderBy('bank_rek')->get();

        $this->asRole('finance')->post(route('doPaidTransaction', $t), $this->payload())->assertSessionHasNoErrors();

        $this->assertSame('Paid', DB::table('transactions')->where('id', $t->id)->value('payment_status'));
        $this->assertEquals($before, DB::table('rekenings')->orderBy('bank_rek')->get());
    }

    public function test_a_second_submit_that_passed_the_page_check_does_not_mark_it_paid_again(): void
    {
        $t = $this->transaction(['student' => 'Finrace Kid', 'transaction_quota' => 4]);
        $maxQuota = DB::table('students')->where('id', $t->students_id)->value('MaxQuota');
        // The first submit settles the row right after route binding loaded it as Unpaid for the second one.
        $done = false;
        Transaction::retrieved(function (Transaction $model) use ($t, &$done) {
            if (! $done && $model->id === $t->id) {
                $done = true;
                DB::table('transactions')->where('id', $t->id)
                    ->update(['payment_status' => 'Paid', 'transaction_payment' => '2026-10-01', 'transaction_type' => 'Cash']);
            }
        });

        $this->asRole('finance')->post(route('doPaidTransaction', $t), ['inputQuota' => 8] + $this->payload())
            ->assertRedirect()->assertSessionHas('error', 'This transaction is already settled.');

        $row = DB::table('transactions')->where('id', $t->id)->first();
        $this->assertSame('2026-10-01', substr((string) $row->transaction_payment, 0, 10));
        $this->assertSame('Cash', $row->transaction_type);
        $this->assertEquals(4, $row->transaction_quota);
        $this->assertEquals($maxQuota, DB::table('students')->where('id', $t->students_id)->value('MaxQuota'));
    }
}
