<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Rule content is rich text shown as raw HTML on Add Student, so it is sanitized on save and on output. */
class RuleContentTest extends TestCase
{
    use RefreshDatabase;

    private const ATTACK = '<p>Datang tepat waktu</p><script>alert(1)</script>'
        .'<img src="x" onerror="alert(2)"><a href="javascript:alert(3)">klik</a>';

    public function test_saved_rule_content_has_no_script(): void
    {
        $this->actingAs($this->headUser())
            ->post(route('RulesAdd'), ['inputLanguage' => 'Indonesia', 'content' => self::ATTACK])
            ->assertRedirect(route('Rules'));

        $saved = DB::table('rules')->where('lang', 'Indonesia')->latest('id')->value('content');
        $this->assertStringContainsString('<p>Datang tepat waktu</p>', $saved);
        $this->assertStringNotContainsString('<script', $saved);
        $this->assertStringNotContainsString('onerror', $saved);
        $this->assertStringNotContainsString('javascript:', $saved);
    }

    public function test_old_unsafe_content_is_cleaned_when_shown(): void
    {
        DB::table('rules')->insert(['lang' => 'Indonesia', 'content' => '<p>lama</p><script>alert(1)</script>']);

        $html = $this->actingAs($this->headUser())->get(route('headStudentAddPage'))->assertOk()->getContent();

        $this->assertStringContainsString('<p>lama</p>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }

    public function test_accordion_markup_and_logo_placeholder_survive(): void
    {
        DB::table('rules')->insert(['lang' => 'Indonesia', 'content' =>
            '<div class="accordion-item"><h2 class="accordion-header"><button class="accordion-button collapsed" type="button" '
            .'data-bs-toggle="collapse" data-bs-target="#rule-id" aria-expanded="false" aria-controls="rule-id">Bahasa Indonesia</button></h2>'
            .'<div id="rule-id" class="accordion-collapse collapse"><img src="{{asset_url}}" alt="logo"></div></div>']);

        $html = $this->actingAs($this->headUser())->get(route('headStudentAddPage'))->getContent();

        $this->assertStringContainsString('data-bs-toggle="collapse"', $html);
        $this->assertStringContainsString('data-bs-target="#rule-id"', $html);
        $this->assertStringContainsString('src="'.asset('assets/img/logo-hitam.png').'"', $html);
    }

    public function test_content_over_the_limit_is_rejected(): void
    {
        $this->actingAs($this->headUser())
            ->from(route('RulesAddPage'))
            ->post(route('RulesAdd'), ['inputLanguage' => 'Indonesia', 'content' => '<p>'.str_repeat('a', 10001).'</p>'])
            ->assertSessionHasErrors('content');
    }

    public function test_rule_pages_use_local_ckeditor_5_not_the_cdn(): void
    {
        $this->actingAs($this->headUser())->get(route('RulesAddPage'))
            ->assertOk()
            ->assertSee('assets/vendor/ckeditor5/ckeditor5.js', false)
            ->assertDontSee('cdn.ckeditor.com', false);

        $this->assertFileExists(public_path('assets/vendor/ckeditor5/ckeditor5.js'));
        $this->assertFileDoesNotExist(public_path('assets/vendor/ckeditor5.js'));
    }

    private function headUser(): User
    {
        return User::where('role', 'head')->firstOrFail();
    }
}
