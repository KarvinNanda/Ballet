<?php

namespace Tests\Feature\Staff;

use App\Models\Transaction;
use Illuminate\Support\Facades\Gate;

class StaffGatesTest extends StaffTestCase
{
    private const HEAD_ONLY = ['finance.manage', 'stock.manage', 'transaction.delete', 'class.freeze-price', 'attendance.record'];

    public function test_head_only_abilities(): void
    {
        foreach (self::HEAD_ONLY as $ability) {
            $this->assertTrue(Gate::forUser($this->user('head'))->allows($ability), "head: {$ability}");
            $this->assertFalse(Gate::forUser($this->user('admin'))->allows($ability), "admin: {$ability}");
        }
    }

    public function test_admin_may_edit_only_unpaid_transactions(): void
    {
        $paid = Transaction::where('payment_status', 'Paid')->firstOrFail();
        $unpaid = Transaction::where('payment_status', '!=', 'Paid')->firstOrFail();

        $this->assertFalse(Gate::forUser($this->user('admin'))->allows('transaction.edit-paid', $paid));
        $this->assertTrue(Gate::forUser($this->user('admin'))->allows('transaction.edit-paid', $unpaid));
        $this->assertTrue(Gate::forUser($this->user('head'))->allows('transaction.edit-paid', $paid));
    }

    /** Allowlist: admin edits only 'Unpaid'; legacy 'lunas' and NULL rows are treated as not editable. */
    public function test_edit_paid_is_an_allowlist(): void
    {
        $row = fn (?string $status) => (new Transaction)->forceFill(['payment_status' => $status]);

        foreach (['lunas', null, 'Paid'] as $status) {
            $this->assertFalse(Gate::forUser($this->user('admin'))->allows('transaction.edit-paid', $row($status)), 'admin: '.var_export($status, true));
            $this->assertTrue(Gate::forUser($this->user('head'))->allows('transaction.edit-paid', $row($status)), 'head: '.var_export($status, true));
        }
        foreach (['teacher', 'finance'] as $role) {
            foreach (['Unpaid', 'Paid', 'lunas', null] as $status) {
                $this->assertFalse(Gate::forUser($this->user($role))->allows('transaction.edit-paid', $row($status)), "{$role}: ".var_export($status, true));
            }
        }
    }
}
