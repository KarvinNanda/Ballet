<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Staff\StaffTestCase;

class ProfilePagesTest extends StaffTestCase
{
    private function member(): User
    {
        $user = User::factory()->create(['role' => 'teacher', 'name' => 'Prof Ile', 'email' => 'prof.ile@example.com']);
        DB::table('users')->where('id', $user->id)->update([
            'dob' => '1990-05-04', 'phone' => '081234567890', 'address' => 'Jl. Mawar "Indah" No. 5 & 6',
        ]);

        return $user->fresh();
    }

    public function test_profile_page_shows_name_and_birth_date_as_text_and_the_editable_fields(): void
    {
        $html = $this->actingAs($this->member())->get(route('change-profile-page'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">My profile</h1>', $html);
        $this->assertStringContainsString('<p class="form-static">Prof Ile</p>', $html);
        $this->assertStringContainsString('<p class="form-static">04 May 1990</p>', $html);
        $this->assertStringNotContainsString('name="name"', $html);
        $this->assertStringNotContainsString('name="dob"', $html);
        $this->assertStringContainsString('action="'.route('change-profile').'"', $html);
        $this->assertStringContainsString('<textarea id="field-address" name="address" class="form-control" autocomplete="street-address" rows="3" required', $html);
        $this->assertStringContainsString('>Jl. Mawar &quot;Indah&quot; No. 5 &amp; 6</textarea>', $html);
        $this->assertStringContainsString('<input type="tel" id="field-phone" name="phone" value="081234567890" class="form-control" autocomplete="tel" required', $html);
        $this->assertStringContainsString('<input type="email" id="field-email" name="email" value="prof.ile@example.com" class="form-control" autocomplete="email" required', $html);
        $this->assertStringContainsString('Save changes', $html);
    }

    public function test_posting_the_rendered_profile_form_unchanged_keeps_the_user_row(): void
    {
        $user = $this->member();
        $before = (array) DB::table('users')->where('id', $user->id)->first();

        $html = $this->actingAs($user)->get(route('change-profile-page'))->assertOk()->getContent();
        $this->post(route('change-profile'), $this->formFields($html, route('change-profile')))
            ->assertSessionHasNoErrors()->assertRedirect(route('change-profile-page'));

        $after = (array) DB::table('users')->where('id', $user->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertEquals($before, $after);
    }

    public function test_password_page_has_two_password_fields(): void
    {
        $html = $this->actingAs($this->member())->get(route('change-password-page'))->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="page-title">Change password</h1>', $html);
        $this->assertStringContainsString('action="'.route('change-password').'"', $html);
        $this->assertStringContainsString('<input type="password" id="field-new_password" name="new_password" value="" class="form-control" autocomplete="new-password" required', $html);
        $this->assertStringContainsString('aria-describedby="field-new_password-help"', $html);
        $this->assertStringContainsString('<div id="field-new_password-help" class="form-text">At least 8 characters.</div>', $html);
        $this->assertStringContainsString('<input type="password" id="field-confirm_password" name="confirm_password" value="" class="form-control" autocomplete="new-password" required', $html);
        $this->assertStringNotContainsString('current_password', $html);
    }

    public function test_a_rejected_password_is_neither_flashed_nor_shown(): void
    {
        $this->actingAs($this->member())->from(route('change-password-page'))
            ->post(route('change-password'), ['new_password' => 'short7c', 'confirm_password' => 'short7c'])
            ->assertRedirect(route('change-password-page'))
            ->assertSessionHasErrors('new_password');

        $this->assertFalse(session()->hasOldInput('new_password'));
        $this->assertFalse(session()->hasOldInput('confirm_password'));
        $this->get(route('change-password-page'))->assertOk()->assertDontSee('short7c');
    }

    public function test_password_fields_never_render_a_value_even_with_old_input(): void
    {
        $this->actingAs($this->member())
            ->withSession(['_old_input' => ['new_password' => 'leak-Pa55word', 'confirm_password' => 'leak-Pa55word']])
            ->get(route('change-password-page'))->assertOk()
            ->assertDontSee('leak-Pa55word')
            ->assertSee('name="new_password" value=""', false)
            ->assertSee('name="confirm_password" value=""', false);
    }

    public function test_both_pages_render_for_every_role_and_a_missing_birth_date(): void
    {
        foreach (['admin', 'head', 'teacher', 'finance'] as $role) {
            $this->asRole($role)->get(route('change-profile-page'))->assertOk()->assertSee('<h1 class="page-title">My profile</h1>', false);
            $this->asRole($role)->get(route('change-password-page'))->assertOk()->assertSee('<h1 class="page-title">Change password</h1>', false);
        }

        foreach (['buyer', null] as $role) {
            $this->flushSession();
            $this->app['auth']->forgetGuards();
            $this->actingAs(User::factory()->create(['role' => $role])); // factory users have no dob, phone or address
            $this->get(route('change-profile-page'))->assertOk()->assertSee('<p class="form-static">-</p>', false);
            $this->get(route('change-password-page'))->assertOk();
        }
    }
}
