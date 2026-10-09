<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class HeadAdminPagesTest extends StaffTestCase
{
    private function newAdmin(): User
    {
        $user = User::factory()->create(['role' => 'admin', 'name' => 'Rina Admin']);
        DB::table('users')->where('id', $user->id)->update(['dob' => '2000-10-10', 'phone' => '081299990002', 'address' => 'Jl. Admin 2']);

        return $user->fresh();
    }

    public function test_list_shows_age_and_a_confirmed_delete_but_not_the_address(): void
    {
        $this->travelTo(Carbon::parse('2026-10-09 12:00:00'));
        $admin = $this->newAdmin();

        $html = $this->asRole('head')->get(route('headAdminPage'))->assertOk()->getContent();
        $row = $this->rowFor($html, 'Rina Admin');

        $this->assertStringContainsString('<h1 class="page-title">Admin accounts</h1>', $html);
        $this->assertStringContainsString('href="'.route('headAdminAddPage').'"', $html);
        $this->assertMatchesRegularExpression('/<td>\s*25\s*<\/td>/', $row);
        $this->assertStringContainsString('href="'.route('headAdminUpdatePage', $admin).'"', $row);
        $this->assertStringContainsString('action="'.route('AdminDelete', $admin).'" data-confirm="Delete admin Rina Admin? This cannot be undone."', $row);
        $this->assertStringNotContainsString('Jl. Admin 2', $html);
    }

    public function test_search_is_a_get_form_to_the_list_and_keeps_the_term(): void
    {
        $this->newAdmin();
        User::factory()->create(['role' => 'admin', 'name' => 'Other Person']);

        $this->asRole('head')->get(route('headAdminPage'))
            ->assertSee('<form method="GET" action="'.route('headAdminPage').'" role="search"', false);

        $this->get(route('headAdminPage', ['search' => 'Rina']))->assertOk()
            ->assertSee('name="search" value="Rina"', false)
            ->assertSee('Rina Admin')
            ->assertDontSee('Other Person');
    }

    public function test_page_two_of_a_search_keeps_the_search_term(): void
    {
        foreach (range(1, 6) as $n) {
            User::factory()->create(['role' => 'admin', 'name' => "Paging Admin {$n}"]);
        }
        User::factory()->create(['role' => 'admin', 'name' => 'Unrelated Admin']);

        $html = $this->asRole('head')->get(route('headAdminPage', ['search' => 'Paging']))->assertOk()->getContent();
        $this->assertStringContainsString('search=Paging', $html);
        $this->assertMatchesRegularExpression('/href="[^"]*page=2[^"]*search=Paging|href="[^"]*search=Paging[^"]*page=2/', $html);

        $page2 = $this->get(route('headAdminPage', ['search' => 'Paging', 'page' => 2]))->assertOk()->getContent();
        $this->assertStringContainsString('Paging Admin', $page2);
        $this->assertStringNotContainsString('Unrelated Admin', $page2);
    }

    public function test_delete_from_a_search_result_returns_to_the_get_list(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'name' => 'Paging Admin X']);
        $list = route('headAdminPage', ['search' => 'Paging']);

        $this->asRole('head')->from($list)->post(route('AdminDelete', $admin))->assertRedirect($list);
        $this->followingRedirects()->get($list)->assertOk();
        $this->assertDatabaseMissing('users', ['id' => $admin->id]);
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asRole('head')->get(route('headAdminPage', ['search' => 'zzz-nobody']))->assertOk()
            ->assertSeeText('No admin accounts found')->assertDontSee('<table', false);
    }

    public function test_add_page_uses_the_account_fields(): void
    {
        $this->asRole('head')->get(route('headAdminAddPage'))->assertOk()
            ->assertSee('<h1 class="page-title">Add admin</h1>', false)
            ->assertSee('action="'.route('AdminAdd').'"', false)
            ->assertSee('name="inputEmail"', false)
            ->assertDontSee('name="inputBonus"', false);
    }

    public function test_update_form_labels_the_bonus_and_says_no_report_uses_it(): void
    {
        $admin = $this->user('admin');
        DB::table('users')->where('id', $admin->id)->update(['percent' => 15]);
        $html = $this->asRole('head')->get(route('headAdminUpdatePage', $admin))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Update admin</h1>', $html);
        $this->assertStringContainsString('Bonus %', $html);
        $this->assertStringContainsString('Stored on the account; not used by any report.', $html);
        $this->assertStringContainsString('name="inputBonus" value="15" class="form-control" min="0" max="100" required', $html);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_every_column(): void
    {
        $admin = $this->user('admin');
        DB::table('users')->where('id', $admin->id)->update(['percent' => 15, 'address' => "Jl. Mawar 1\nBlok B"]);
        $before = (array) DB::table('users')->where('id', $admin->id)->first();

        $update = route('headAdminUpdate', $admin);
        $html = $this->asRole('head')->get(route('headAdminUpdatePage', $admin))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();

        $after = (array) DB::table('users')->where('id', $admin->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertSame($before, $after);
    }
}
