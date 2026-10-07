<?php

namespace Tests\Unit;

use App\Support\HtmlSanitizer;
use Tests\TestCase;

/** Needs the app container only for config('app.url'); no database. */
class HtmlSanitizerTest extends TestCase
{
    private function clean(string $html): string
    {
        return (new HtmlSanitizer())->clean($html);
    }

    public function test_scripts_and_event_handlers_are_removed(): void
    {
        $out = $this->clean('<p onclick="steal()">Hi<script>alert(1)</script></p>');

        $this->assertStringNotContainsString('script', $out);
        $this->assertStringNotContainsString('onclick', $out);
        $this->assertStringContainsString('<p>Hi</p>', $out);
    }

    public function test_javascript_urls_are_removed(): void
    {
        $out = $this->clean('<a href="javascript:alert(1)">x</a>');

        $this->assertStringNotContainsString('javascript:', $out);
    }

    public function test_style_attributes_and_unknown_classes_are_removed(): void
    {
        $out = $this->clean('<span class="accordion evil" style="position:fixed">x</span>');

        $this->assertStringNotContainsString('style=', $out);
        $this->assertStringNotContainsString('evil', $out);
        $this->assertStringContainsString('class="accordion"', $out);
    }

    public function test_formatting_is_kept(): void
    {
        $html = '<p><strong>Bold</strong> <em>it</em></p><ul><li>One</li></ul>';

        $this->assertSame($html, $this->clean($html));
    }

    public function test_ids_and_their_references_get_one_prefix(): void
    {
        $out = $this->clean(
            '<button data-bs-toggle="collapse" data-bs-target="#c1" aria-controls="c1">T</button><div id="c1" class="collapse">B</div>'
        );

        $this->assertStringContainsString('data-bs-target="#rule-c1"', $out);
        $this->assertStringContainsString('aria-controls="rule-c1"', $out);
        $this->assertStringContainsString('id="rule-c1"', $out);
    }

    public function test_cleaning_twice_gives_the_same_result(): void
    {
        $once = $this->clean('<div id="a" class="accordion"><button data-bs-target="#a">x</button></div>');

        $this->assertSame($once, $this->clean($once));
    }

    public function test_buttons_never_submit_the_surrounding_form(): void
    {
        $this->assertStringContainsString('<button type="button"', $this->clean('<button class="accordion-button">x</button>'));
    }

    public function test_null_gives_an_empty_string(): void
    {
        $this->assertSame('', (new HtmlSanitizer())->clean(null));
    }
}
