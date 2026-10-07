<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_layout_uses_theme_and_local_assets_only(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin')
            ->assertOk()
            ->assertSee('assets/css/theme.css', false)
            ->assertSee('assets/vendor/jquery/jquery.min.js', false)
            ->assertDontSee('code.jquery.com', false)
            ->assertDontSee('cdn.jsdelivr.net', false)
            ->assertDontSee('assets/css/style.css', false);
    }

    public function test_jquery_loads_before_page_content(): void
    {
        $html = $this->actingAs($this->user('admin'))->get('/admin/transaction/add')->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'id="main"'), strpos($html, 'jquery.min.js'));
    }

    public function test_rule_pages_load_ckeditor(): void
    {
        $this->actingAs($this->user('head'))->get('/head/report/rule/add')
            ->assertOk()
            ->assertSee('assets/vendor/ckeditor5/ckeditor5.js', false);
    }

    public function test_flash_message_is_shown_as_toast(): void
    {
        $this->actingAs($this->user('admin'))->withSession(['msg' => 'Data tersimpan'])->get('/admin')
            ->assertSee('Data tersimpan')
            ->assertSee('class="toast', false);
    }

    public function test_error_message_is_shown_as_a_red_toast(): void
    {
        $html = $this->actingAs($this->user('admin'))->withSession(['error' => 'Jadwal sudah diabsen'])->get('/admin')->getContent();

        $this->assertStringContainsString('Jadwal sudah diabsen', $html);
        $this->assertStringContainsString('toast toast-error', $html);
        $this->assertStringContainsString('bi-exclamation-triangle-fill', $html);
    }

    public function test_sidebar_shows_role_menu_and_marks_active_item(): void
    {
        $this->actingAs($this->user('head'))->get('/head')
            ->assertSee(route('headAdminPage'), false)
            ->assertSee('app-nav-link active', false);

        $this->actingAs($this->user('teacher'))->get('/teacher')
            ->assertDontSee(route('headAdminPage'), false);
    }

    public function test_sidebar_groups_collapse_and_only_the_active_group_starts_open(): void
    {
        $html = $this->actingAs($this->user('admin'))->get('/admin/class/add')->assertOk()->getContent();

        // Sub-page of Class: Class link is active, its group (Master) is open, other groups are closed.
        $this->assertMatchesRegularExpression('#href="'.preg_quote(route('adminClassView'), '#').'"[^>]*class="app-nav-link active"#', $html);
        $this->assertMatchesRegularExpression('#id="nav-group-master" class="collapse show"#', $html);
        $this->assertMatchesRegularExpression('#id="nav-group-report" class="collapse"#', $html);
        $this->assertStringContainsString('data-bs-target="#nav-group-report" aria-expanded="false"', $html);
    }

    public function test_user_without_menu_can_open_profile(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'buyer']))->get('/profile')->assertOk();
        $this->actingAs(User::factory()->create(['role' => null]))->get('/profile')->assertOk();
    }

    public function test_logout_is_a_post_form(): void
    {
        $this->actingAs($this->user('admin'))->get('/admin')
            ->assertSee('action="'.route('logout').'"', false)
            ->assertSee('method="POST"', false);
    }

    private function user(string $role): User
    {
        return User::where('role', $role)->firstOrFail();
    }
}
