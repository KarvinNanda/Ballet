<?php

namespace Tests\Feature\Staff;

use App\Models\Student;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionPagesTest extends StaffTestCase
{
    /** A transaction of a new active student (LongName from 'student'), in the first class. */
    protected function transaction(array $attributes = []): Transaction
    {
        $student = Student::factory()->create(['LongName' => $attributes['student'] ?? 'Tx Kid', 'Status' => 'aktif']);
        unset($attributes['student']);

        $id = DB::table('transactions')->insertGetId(array_merge([
            'students_id' => $student->id,
            'class_transactions_id' => DB::table('class_transactions')->orderBy('id')->value('id'),
            'transaction_date' => '2026-09-01',
            'payment_status' => 'Unpaid',
            'price' => 350000,
            'discount' => '0',
            'transaction_quota' => 4,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return Transaction::findOrFail($id);
    }

    public function test_status_filter_shows_only_that_status(): void
    {
        $this->transaction(['student' => 'Txfilter Unpaid']);
        $this->transaction(['student' => 'Txfilter Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $this->asRole('head')->get(route('head.transaction.index', ['search' => 'Txfilter', 'status' => 'Unpaid']))
            ->assertOk()->assertSee('Txfilter Unpaid')->assertDontSee('Txfilter Paid')
            ->assertSee('aria-current="page">Unpaid</a>', false);
    }

    public function test_sort_keeps_search_and_status(): void
    {
        $this->transaction(['student' => 'Txsort Unpaid']);
        $this->transaction(['student' => 'Txsort Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);
        $this->transaction(['student' => 'Other Kid']);

        $this->asRole('admin')->get(route('admin.transaction.sort', ['column' => 'price', 'direction' => 'asc', 'search' => 'Txsort', 'status' => 'Paid']))
            ->assertOk()->assertSee('Txsort Paid')->assertDontSee('Txsort Unpaid')->assertDontSee('Other Kid');
    }

    public function test_sort_links_carry_the_filters(): void
    {
        $this->transaction(['student' => 'Ani Txlink', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $this->asRole('head')->get(route('head.transaction.index', ['search' => 'Ani', 'status' => 'Paid']))
            ->assertSee('/head/transaction/sorting/price/asc?search=Ani&status=Paid');
    }

    public function test_unknown_status_is_ignored(): void
    {
        $this->asRole('head')->get(route('head.transaction.index', ['status' => 'lunas']))->assertOk();
        $this->asRole('head')->get(route('head.transaction.sort', ['column' => 'price', 'direction' => 'asc', 'status' => 'x']))->assertOk();
    }

    public function test_list_shows_class_and_discounted_totals(): void
    {
        $this->transaction(['student' => 'Txtotal Percent', 'discount' => '10%']);
        $this->transaction(['student' => 'Txtotal Amount', 'discount' => '50000']);
        $className = DB::table('class_transactions')->join('class_types', 'class_types.id', 'class_transactions.class_type_id')
            ->orderBy('class_transactions.id')->value('class_types.class_name');

        $html = $this->asRole('head')->get(route('head.transaction.index', ['search' => 'Txtotal']))->getContent();

        $this->assertStringContainsString('Rp315,000', $this->rowFor($html, 'Txtotal Percent'));
        $this->assertStringContainsString('Rp300,000', $this->rowFor($html, 'Txtotal Amount'));
        $this->assertStringContainsString(e($className), $this->rowFor($html, 'Txtotal Amount'));
    }

    public function test_list_survives_an_invalid_stored_discount(): void
    {
        $this->transaction(['student' => 'Txbad Kid', 'discount' => 'abc']);

        $this->asRole('admin')->get(route('admin.transaction.index', ['search' => 'Txbad']))
            ->assertOk()->assertSeeText('Invalid discount')->assertSeeText('Rp350,000');
    }

    public function test_empty_result_shows_empty_state(): void
    {
        $this->asRole('admin')->get(route('admin.transaction.index', ['search' => 'zzz-no-such-kid']))
            ->assertSeeText('No transactions found')->assertDontSee('<table', false);
    }

    public function test_row_menu_follows_the_gates(): void
    {
        $unpaid = $this->transaction(['student' => 'Txgate Unpaid']);
        $paid = $this->transaction(['student' => 'Txgate Paid', 'payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $admin = $this->asRole('admin')->get(route('admin.transaction.index', ['search' => 'Txgate']))->getContent();
        $this->assertStringContainsString(route('admin.transaction.edit', $unpaid), $this->rowFor($admin, 'Txgate Unpaid'));
        $this->assertStringNotContainsString(route('admin.transaction.edit', $paid), $this->rowFor($admin, 'Txgate Paid'));
        $this->assertStringNotContainsString('Delete', $admin);

        $head = $this->asRole('head')->get(route('head.transaction.index', ['search' => 'Txgate']))->getContent();
        $this->assertStringContainsString(route('head.transaction.edit', $paid), $this->rowFor($head, 'Txgate Paid'));
        $this->assertStringContainsString('data-confirm="Delete this transaction of Txgate Paid? This cannot be undone."', $this->rowFor($head, 'Txgate Paid'));
    }

    public function test_detail_shows_billing_and_the_server_total(): void
    {
        $t = $this->transaction(['student' => 'Txdetail Kid', 'discount' => '10%', 'desc' => 'Term 3']);

        $this->asRole('head')->get(route('head.transaction.show', $t))->assertOk()
            ->assertSeeText('Rp315,000')->assertSeeText('10%')->assertSeeText('Term 3')
            ->assertSee('href="'.route('head.student.show', ['student' => $t->students_id, 'tab' => 'transactions']).'"', false)
            ->assertDontSee('<script>', false);
    }

    public function test_detail_survives_an_invalid_stored_discount(): void
    {
        $t = $this->transaction(['discount' => 'abc']);
        $this->asRole('admin')->get(route('admin.transaction.show', $t))->assertOk()->assertSeeText('Invalid discount');
    }

    public function test_detail_actions_follow_the_gates(): void
    {
        $paid = $this->transaction(['payment_status' => 'Paid', 'transaction_payment' => '2026-09-02']);

        $this->asRole('admin')->get(route('admin.transaction.show', $paid))
            ->assertDontSee(route('admin.transaction.edit', $paid), false)
            ->assertDontSee(route('admin.transaction.destroy', $paid), false);

        $this->asRole('head')->get(route('head.transaction.show', $paid))
            ->assertSee(route('head.transaction.edit', $paid), false)
            ->assertSee('action="'.route('head.transaction.destroy', $paid).'"', false);
    }

    public function test_delete_from_detail_returns_to_the_list(): void
    {
        $t = $this->transaction();

        $this->asRole('head')->from(route('head.transaction.show', $t))
            ->post(route('head.transaction.destroy', $t), ['return_url' => route('head.transaction.index')])
            ->assertRedirect(route('head.transaction.index'));
        $this->assertNull(Transaction::find($t->id));
    }

    public function test_delete_from_the_list_still_goes_back(): void
    {
        $t = $this->transaction();
        $this->asRole('head')->from(route('head.transaction.index'))->post(route('head.transaction.destroy', $t))
            ->assertRedirect(route('head.transaction.index'));
    }

    public function test_add_page_carries_class_prices_and_loads_the_page_script(): void
    {
        $class = DB::table('class_transactions as ct')->join('class_types', 'class_types.id', 'ct.class_type_id')
            ->where('ct.status', 'aktif')->orderBy('ct.id')->first(['ct.id', 'ct.class_transaction_price']);

        $this->asRole('admin')->get(route('admin.transaction.create'))->assertOk()
            ->assertSee('<option value="'.$class->id.'" data-price="'.$class->class_transaction_price.'"', false)
            ->assertSee('data-transaction-form', false)
            ->assertSee('assets/js/pages/transaction-form.js', false)
            ->assertDontSee('get-price', false);
    }

    public function test_price_endpoint_is_gone(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('head.transaction.price'));
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('admin.transaction.price'));
    }

    public function test_add_form_has_every_field_the_store_request_reads(): void
    {
        $response = $this->asRole('head')->get(route('head.transaction.create'));
        foreach (array_keys((new \App\Http\Requests\Staff\StoreTransactionRequest)->rules()) as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_every_column(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Tx Bank']);
        $t = $this->transaction(['discount' => '10%', 'desc' => 'Term 3', 'transaction_type' => 'Transfer']);
        DB::table('students')->where('id', $t->students_id)->update(['bank_rek' => '5552223333']);
        DB::table('rekenings')->insert(['bank_rek' => '5552223333', 'nama_pengirim' => 'Tx Parent', 'banks_id' => $bank]);
        $before = (array) DB::table('transactions')->where('id', $t->id)->first();
        $rekBefore = (array) DB::table('rekenings')->where('bank_rek', '5552223333')->first();

        $update = route('head.transaction.update', $t);
        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertSessionMissing('error')->assertRedirect();

        $after = (array) DB::table('transactions')->where('id', $t->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertEquals($before, $after);
        $this->assertEquals($rekBefore, (array) DB::table('rekenings')->where('bank_rek', '5552223333')->first());
    }

    public function test_update_form_shows_zero_for_an_empty_discount_and_the_server_total(): void
    {
        $t = $this->transaction(['discount' => null]);

        $this->asRole('head')->get(route('head.transaction.edit', $t))
            ->assertSee('name="inputDisc" value="0"', false)
            ->assertSee('data-total-output', false)
            ->assertSeeText('Rp350,000');
    }

    public function test_update_form_keeps_an_unknown_status_visible(): void
    {
        $t = $this->transaction(['payment_status' => 'lunas']);

        $this->asRole('head')->get(route('head.transaction.edit', $t))
            ->assertSee('<option value="lunas" selected>lunas</option>', false);
    }

    public function test_apply_all_checkbox_is_explicit(): void
    {
        $t = $this->transaction(['student' => 'Txall Kid']);
        $className = DB::table('class_transactions')->join('class_types', 'class_types.id', 'class_transactions.class_type_id')
            ->where('class_transactions.id', $t->class_transactions_id)->value('class_types.class_name');

        $this->asRole('head')->get(route('head.transaction.edit', $t))
            ->assertSee('name="all_transaction" value="1"', false)
            ->assertSee('data-apply-all', false)
            ->assertSeeText('Also apply these values to every other transaction of Txall Kid in '.$className.' and mark them Paid')
            ->assertSeeText('Only applies when a payment date is filled in. It overwrites due date, price, discount, quota, description and payment type on those transactions.')
            ->assertSee('data-confirm-message="'.e('Overwrite every other transaction of Txall Kid in '.$className.' with these values and mark them Paid?').'"', false)
            ->assertSee('assets/js/pages/transaction-form.js', false);
    }

    public function test_update_page_explains_when_a_payment_date_is_required(): void
    {
        $t = $this->transaction();

        $this->asRole('head')->get(route('head.transaction.edit', $t))
            ->assertSeeText('Required when Status is Paid; leave empty when Status is Unpaid.');
    }

    public function test_unpaid_row_with_a_stored_payment_date_is_not_silently_marked_paid(): void
    {
        $t = $this->transaction(['transaction_payment' => '2026-09-05']);
        $quota = DB::table('students')->where('id', $t->students_id)->value('Quota');
        $before = (array) DB::table('transactions')->where('id', $t->id)->first();

        $update = route('head.transaction.update', $t);
        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))
            ->assertSessionHasErrors(['inputTanggalBayar' => 'Clear the payment date or set Status to Paid.']);

        $after = (array) DB::table('transactions')->where('id', $t->id)->first();
        $this->assertEquals($before, $after);
        $this->assertSame('Unpaid', $after['payment_status']);
        $this->assertEquals($quota, DB::table('students')->where('id', $t->students_id)->value('Quota'));
    }

    public function test_paid_status_with_a_payment_date_saves_as_paid(): void
    {
        $t = $this->transaction();

        $update = route('head.transaction.update', $t);
        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, array_merge($this->formFields($html, $update), ['inputStatus' => 'Paid', 'inputTanggalBayar' => '2026-09-07']))
            ->assertSessionHasNoErrors()->assertRedirect();

        $row = DB::table('transactions')->where('id', $t->id)->first();
        $this->assertSame('Paid', $row->payment_status);
        $this->assertSame('2026-09-07', substr($row->transaction_payment, 0, 10));
    }

    public function test_unpaid_status_with_an_empty_payment_date_stays_unpaid(): void
    {
        $t = $this->transaction();
        $quota = DB::table('students')->where('id', $t->students_id)->value('Quota');

        $update = route('head.transaction.update', $t);
        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, array_merge($this->formFields($html, $update), ['inputStatus' => 'Unpaid', 'inputTanggalBayar' => '']))
            ->assertSessionHasNoErrors()->assertRedirect();

        $row = DB::table('transactions')->where('id', $t->id)->first();
        $this->assertSame('Unpaid', $row->payment_status);
        $this->assertNull($row->transaction_payment);
        $this->assertEquals($quota, DB::table('students')->where('id', $t->students_id)->value('Quota'));
    }

    public function test_unpaying_a_row_leaves_no_stale_date_on_the_next_edit(): void
    {
        $t = $this->transaction();
        $update = route('head.transaction.update', $t);

        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, array_merge($this->formFields($html, $update), ['inputStatus' => 'Paid', 'inputTanggalBayar' => '2026-09-07']))
            ->assertSessionHasNoErrors();
        $html = $this->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, array_merge($this->formFields($html, $update), ['inputStatus' => 'Unpaid', 'inputTanggalBayar' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull(DB::table('transactions')->where('id', $t->id)->value('transaction_payment'));

        $html = $this->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();
    }

    public function test_bulk_update_with_paid_status_and_a_date_still_works(): void
    {
        $t = $this->transaction();
        $siblingId = DB::table('transactions')->insertGetId([
            'students_id' => $t->students_id, 'class_transactions_id' => $t->class_transactions_id,
            'transaction_date' => '2026-10-01', 'payment_status' => 'Unpaid', 'price' => 111, 'discount' => '0',
            'transaction_quota' => 4, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $update = route('head.transaction.update', $t);
        $html = $this->asRole('head')->get(route('head.transaction.edit', $t))->assertOk()->getContent();
        $this->post($update, array_merge($this->formFields($html, $update), ['inputStatus' => 'Paid', 'inputTanggalBayar' => '2026-09-07', 'all_transaction' => '1']))
            ->assertSessionHasNoErrors()->assertRedirect();

        foreach ([$t->id, $siblingId] as $id) {
            $row = DB::table('transactions')->where('id', $id)->first();
            $this->assertSame('Paid', $row->payment_status);
            $this->assertSame('2026-09-07', substr($row->transaction_payment, 0, 10));
        }
    }

    public function test_add_page_prefills_the_locked_price_of_a_frozen_class(): void
    {
        $id = DB::table('class_transactions')->where('status', 'aktif')->orderBy('id')->value('id');
        DB::table('class_transactions')->where('id', $id)->update(['is_freeze' => 1, 'class_transaction_price' => 123456, 'status' => 'aktif']);

        $this->asRole('admin')->get(route('admin.transaction.create'))->assertOk()
            ->assertSee('<option value="'.$id.'" data-price="123456"', false);
    }

    public function test_update_with_array_values_does_not_500_on_the_redisplayed_form(): void
    {
        $t = $this->transaction();
        $edit = route('head.transaction.edit', $t);

        $this->asRole('head')->from($edit)->followingRedirects()
            ->post(route('head.transaction.update', $t), ['inputDisc' => ['x'], 'inputPrice' => ['y']])
            ->assertOk();
    }

    public function test_add_page_survives_array_old_class_input(): void
    {
        $this->asRole('head')->from(route('head.transaction.create'))->followingRedirects()
            ->post(route('head.transaction.store'), ['class' => ['x'], 'nis' => ['y']])
            ->assertOk();
    }

    public function test_detail_delete_form_returns_to_the_list(): void
    {
        $t = $this->transaction();

        $this->asRole('head')->get(route('head.transaction.show', $t))->assertOk()
            ->assertSee('<input type="hidden" name="return_url" value="'.route('head.transaction.index').'">', false);
    }
}
