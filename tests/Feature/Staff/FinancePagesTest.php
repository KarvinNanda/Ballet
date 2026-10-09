<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class FinancePagesTest extends StaffTestCase
{
    private function newFinance(): User
    {
        $user = User::factory()->create(['role' => 'finance', 'name' => 'Fina Kasir']);
        DB::table('users')->where('id', $user->id)->update(['dob' => '2000-10-10', 'phone' => '081299990001', 'address' => 'Jl. Kasir 1']);

        return $user->fresh();
    }

    public function test_list_shows_age_and_contact_but_not_the_address(): void
    {
        $this->travelTo(Carbon::parse('2026-10-09 12:00:00'));
        $this->newFinance();

        $html = $this->asRole('head')->get(route('head.finance.index', ['search' => 'Fina']))->assertOk()->getContent();
        $row = $this->rowFor($html, 'Fina Kasir');

        $this->assertMatchesRegularExpression('/<td>\s*25\s*<\/td>/', $row);
        $this->assertStringContainsString('<td>081299990001</td>', $row);
        $this->assertStringNotContainsString('Jl. Kasir 1', $html);
    }

    public function test_admin_sees_the_list_and_add_but_no_update_or_delete(): void
    {
        $finance = $this->newFinance();

        $html = $this->asRole('admin')->get(route('admin.finance.index'))->assertOk()->getContent();

        $this->assertStringContainsString('href="'.route('admin.finance.create').'"', $html);
        $this->assertStringContainsString('Add finance account', $html);
        $this->assertStringNotContainsString(route('admin.finance.edit', $finance), $html);
        $this->assertStringNotContainsString(route('admin.finance.destroy', $finance), $html);
        $this->assertStringNotContainsString('data-confirm', $html);
    }

    public function test_head_row_has_update_and_a_confirmed_delete(): void
    {
        $finance = $this->newFinance();
        $row = $this->rowFor($this->asRole('head')->get(route('head.finance.index'))->getContent(), 'Fina Kasir');

        $this->assertStringContainsString('href="'.route('head.finance.edit', $finance).'"', $row);
        $this->assertStringContainsString('action="'.route('head.finance.destroy', $finance).'" data-confirm="Delete Fina Kasir? This cannot be undone."', $row);
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asRole('admin')->get(route('admin.finance.index', ['search' => 'zzz-nobody']))
            ->assertOk()->assertSeeText('No finance accounts found')->assertDontSee('<table', false);
    }

    public function test_add_page_uses_the_account_fields(): void
    {
        $this->asRole('admin')->get(route('admin.finance.create'))->assertOk()
            ->assertSee('<h1 class="page-title">Add finance account</h1>', false)
            ->assertSee('name="inputName"', false)
            ->assertDontSee('name="inputBonus"', false);
    }

    public function test_update_form_labels_the_bonus_and_says_no_report_uses_it(): void
    {
        $html = $this->asRole('head')->get(route('head.finance.edit', $this->user('finance')))->assertOk()->getContent();

        $this->assertStringContainsString('Bonus %', $html);
        $this->assertStringContainsString('Stored on the account; not used by any report.', $html);
        $this->assertStringContainsString('name="inputBonus" value="0" class="form-control" min="0" max="100" required', $html);
        $this->assertSame(1, substr_count($html, 'name="inputAddress"'));
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_every_column(): void
    {
        $finance = $this->user('finance');
        DB::table('users')->where('id', $finance->id)->update(['percent' => 15, 'address' => "Jl. Kenanga 3\nBlok C"]);
        $before = (array) DB::table('users')->where('id', $finance->id)->first();

        $update = route('head.finance.update', $finance);
        $html = $this->asRole('head')->get(route('head.finance.edit', $finance))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();

        $after = (array) DB::table('users')->where('id', $finance->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertSame($before, $after);
    }
}
