<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** String checks only: the real check is the browser pass on a touch device. */
class ThemeCssTest extends TestCase
{
    private function coarseBlock(): string
    {
        $css = file_get_contents(public_path('assets/css/theme.css'));
        $this->assertSame(1, preg_match('/@media \(pointer: coarse\) \{(.*?)\n\}/s', $css, $m), 'no (pointer: coarse) block');

        return $m[1];
    }

    public static function rules(): array
    {
        return [
            'small buttons' => ['.btn-sm { min-height: 44px; }'],
            'menu items' => ['.dropdown-item { min-height: 44px; display: flex; align-items: center; }'],
            'checks (wrap so an error drops below)' => ['.form-check { min-height: 44px; display: flex; flex-wrap: wrap; align-items: center; gap: .5rem; }'],
            'check labels' => ['.form-check-label { flex: 1 1 0; min-width: 0; padding-block: .5rem; }'],
            'check errors on their own line' => ['.form-check > .invalid-feedback { flex-basis: 100%; }'],
            'phone row actions (outranks the 40px card rule)' => ['.table-cards > tbody > tr > td.cell-actions .btn { min-height: 44px; }'],
            'attendance checkbox tap area' => ['.check-hit { min-width: 44px; min-height: 44px; }'],
        ];
    }

    public function test_card_footer_buttons_center_their_label(): void
    {
        $css = file_get_contents(public_path('assets/css/theme.css'));
        // display:block cannot center the label vertically in a 40–44px button; flex can.
        $this->assertMatchesRegularExpression('/td\.cell-actions \.btn \{\s*display: flex; align-items: center; justify-content: center;/', $css);
    }

    public function test_status_badges_never_stretch_in_flex_rows(): void
    {
        $css = file_get_contents(public_path('assets/css/theme.css'));
        $this->assertMatchesRegularExpression('/\.status-badge \{[^}]*align-self: center;/', $css);
    }

    #[DataProvider('rules')]
    public function test_the_coarse_pointer_block_holds_the_44px_rule(string $rule): void
    {
        $this->assertStringContainsString($rule, $this->coarseBlock());
    }
}
