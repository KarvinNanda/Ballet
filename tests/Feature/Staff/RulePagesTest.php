<?php

namespace Tests\Feature\Staff;

use Illuminate\Support\Facades\DB;

class RulePagesTest extends StaffTestCase
{
    private const MODULE = 'assets/js/pages/rule-editor.js';

    public function test_list_has_add_update_and_a_confirmed_delete(): void
    {
        $rule = DB::table('rules')->orderBy('id')->first();
        $html = $this->asRole('head')->get(route('Rules'))->assertOk()->getContent();
        $row = $this->rowFor($html, $rule->lang);

        $this->assertStringContainsString('href="'.route('RulesAddPage').'"', $html);
        $this->assertStringContainsString('href="'.route('RulesUpdatePage', $rule->id).'"', $row);
        $this->assertStringContainsString('action="'.route('RulesDelete', $rule->id).'" data-confirm="'.e('Delete the '.$rule->lang.' rule? This cannot be undone.').'"', $row);
    }

    public function test_empty_list_shows_the_empty_state(): void
    {
        DB::table('rules')->delete();

        $this->asRole('head')->get(route('Rules'))->assertOk()->assertSeeText('No rules yet')->assertDontSee('<table', false);
    }

    public function test_editor_pages_load_the_page_module_and_no_importmap(): void
    {
        $id = DB::table('rules')->value('id');

        foreach ([route('RulesAddPage'), route('RulesUpdatePage', $id)] as $url) {
            $this->asRole('head')->get($url)->assertOk()
                ->assertSee('<script type="module" src="'.asset(self::MODULE).'"></script>', false)
                ->assertSee('<link rel="modulepreload" href="'.asset('assets/vendor/ckeditor5/ckeditor5.js').'">', false)
                ->assertSee('<link rel="stylesheet" href="'.asset('assets/vendor/ckeditor5/ckeditor5.css').'">', false)
                ->assertDontSee('importmap', false)
                ->assertSee('<textarea id="content" name="content" class="form-control"', false);
        }
    }

    public function test_content_textarea_has_no_html_required_because_the_editor_hides_it(): void
    {
        $html = $this->asRole('head')->get(route('RulesAddPage'))->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/<textarea id="content"[^>]*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<textarea id="content"[^>]*\srequired/', $html);
        $this->assertStringContainsString('name="inputLanguage" value="" class="form-control" required', $html);
    }

    public function test_update_page_puts_the_stored_content_in_the_textarea(): void
    {
        $id = DB::table('rules')->insertGetId(['lang' => 'English', 'content' => '<p>Be on time</p>', 'created_at' => now(), 'updated_at' => now()]);

        $this->asRole('head')->get(route('RulesUpdatePage', $id))->assertOk()
            ->assertSee('>&lt;p&gt;Be on time&lt;/p&gt;</textarea>', false)
            ->assertSee('name="inputLanguage" value="English"', false)
            ->assertSee('<h1 class="page-title">Update rule</h1>', false);
    }

    public function test_rule_editor_module_imports_the_local_build_by_relative_path(): void
    {
        $file = public_path(self::MODULE);
        $this->assertFileExists($file);
        $js = file_get_contents($file);

        $this->assertStringContainsString("from '../../vendor/ckeditor5/ckeditor5.js'", $js);
        $this->assertFileExists(dirname($file).'/../../vendor/ckeditor5/ckeditor5.js', 'the relative import must resolve to the vendor build');
        $this->assertStringNotContainsString("from 'ckeditor5'", $js); // a bare specifier needs an importmap
        $this->assertStringContainsString("licenseKey: 'GPL'", $js);
        $this->assertStringContainsString("'data-bs-toggle': /^collapse$/", $js);
        $this->assertStringContainsString("document.querySelector('#content')", $js);
    }

    public function test_every_name_the_module_imports_is_exported_by_the_vendor_build(): void
    {
        $js = file_get_contents(public_path(self::MODULE));
        $this->assertSame(1, preg_match('/import\s*\{([^}]*)\}\s*from/s', $js, $match));
        $names = array_values(array_filter(array_map('trim', explode(',', $match[1]))));
        $vendor = file_get_contents(public_path('assets/vendor/ckeditor5/ckeditor5.js'));

        $this->assertCount(11, $names);
        foreach ($names as $name) {
            $this->assertMatchesRegularExpression('/\bas '.preg_quote($name, '/').'[,}]/', $vendor, "{$name} is not exported by ckeditor5.js");
        }
    }
}
