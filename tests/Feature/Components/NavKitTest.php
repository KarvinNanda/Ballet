<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class NavKitTest extends TestCase
{
    public function test_row_menu_renders_a_labelled_toggle_and_its_items(): void
    {
        $this->blade('<x-row-menu label="More actions for Alya"><li>Item</li></x-row-menu>')
            ->assertSee('aria-label="More actions for Alya"', false)
            ->assertSee('data-bs-popper-config=\'{"strategy":"fixed"}\'', false)
            ->assertSee('<ul class="dropdown-menu dropdown-menu-end">', false)
            ->assertSee('<li>Item</li>', false);
    }

    public function test_row_menu_label_is_escaped(): void
    {
        $this->blade('<x-row-menu :label="$l"><li>x</li></x-row-menu>', ['l' => '"><b>x</b>'])
            ->assertDontSee('<b>x</b>', false);
    }

    public function test_tabs_mark_only_the_active_tab(): void
    {
        $this->blade('<x-tabs :tabs="[\'a\' => \'Alpha\', \'b\' => \'Beta\']" active="b" />')
            ->assertSee('<button class="nav-link" id="tab-a"', false)
            ->assertSee('<button class="nav-link active" id="tab-b"', false)
            ->assertSee('aria-selected="true">Beta</button>', false)
            ->assertSee('data-bs-target="#pane-a"', false);
    }

    public function test_tab_pane_is_shown_only_when_active(): void
    {
        $this->blade('<x-tab-pane name="b" :active="true">x</x-tab-pane>')->assertSee('class="tab-pane fade show active" id="pane-b"', false);
        $this->blade('<x-tab-pane name="a">x</x-tab-pane>')->assertSee('class="tab-pane fade" id="pane-a"', false);
    }

    public function test_error_summary_counts_fields_not_messages(): void
    {
        $this->withViewErrors(['Email' => ['bad', 'worse'], 'Phone1' => 'bad'])
            ->blade('<x-form.error-summary />')
            ->assertSeeText('Please fix the 2 fields marked below.');
    }

    public function test_error_summary_singular(): void
    {
        $this->withViewErrors(['Email' => ['bad', 'worse']])
            ->blade('<x-form.error-summary />')
            ->assertSeeText('Please fix the field marked below.');
    }

    public function test_error_summary_renders_nothing_without_errors(): void
    {
        $this->blade('<x-form.error-summary />')->assertDontSee('alert', false);
    }
}
