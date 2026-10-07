<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Facades\Mail;

class FinanceAccountTest extends StaffTestCase
{
    private function payload(string $email): array
    {
        return ['inputName' => 'New Finance', 'inputEmail' => $email, 'inputDate_of_Birth' => '1995-01-02',
            'inputAddress' => 'Jl. Test', 'inputPhone' => '081234567890'];
    }

    public function test_admin_can_create_a_finance_account(): void
    {
        Mail::fake();
        $this->asRole('admin')->post(route('admin.finance.store'), $this->payload('newfin@example.com'))
            ->assertRedirect(route('admin.finance.index'));
        $this->assertSame('finance', User::where('email', 'newfin@example.com')->value('role'));
    }

    public function test_admin_cannot_update_or_delete(): void
    {
        $finance = $this->user('finance');
        $this->asRole('admin')->get(route('admin.finance.edit', $finance))->assertForbidden();
        $this->post(route('admin.finance.update', $finance), $this->payload($finance->email) + ['inputBonus' => 1])->assertForbidden();
        $this->post(route('admin.finance.destroy', $finance))->assertForbidden();
        $this->assertNotNull($finance->fresh());
    }

    public function test_finance_routes_refuse_non_finance_users(): void // head could edit an admin through these
    {
        $admin = $this->user('admin');
        $this->asRole('head')->get(route('head.finance.edit', $admin))->assertNotFound();
        $this->post(route('head.finance.destroy', $admin))->assertNotFound();
        $this->assertNotNull($admin->fresh());
    }

    public function test_admin_list_has_no_update_or_delete_buttons(): void
    {
        $finance = $this->user('finance');
        $this->asRole('admin')->get(route('admin.finance.index'))
            ->assertOk()->assertSee($finance->name)
            ->assertDontSee(route('admin.finance.edit', $finance));
    }

    public function test_email_must_be_unique(): void
    {
        Mail::fake();
        $this->asRole('head')->post(route('head.finance.store'), $this->payload($this->user('admin')->email))
            ->assertSessionHasErrors('inputEmail');
    }
}
