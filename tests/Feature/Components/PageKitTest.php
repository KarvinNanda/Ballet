<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class PageKitTest extends TestCase
{
    public static function badges(): array
    {
        return [
            'active' => ['aktif', 'Active', 'success'],
            'trial' => ['trial', 'Trial', 'warning'],
            'inactive' => ['non-aktif', 'Inactive', 'neutral'],
            'paid' => ['Paid', 'Paid', 'success'],
            'unpaid' => ['Unpaid', 'Unpaid', 'warning'],
            'legacy value' => ['lunas', 'lunas', 'neutral'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('badges')]
    public function test_status_badge_maps_value_to_label_and_tone(string $status, string $label, string $tone): void
    {
        $this->blade('<x-status-badge :status="$status" />', ['status' => $status])
            ->assertSee('class="status-badge status-badge-'.$tone.'"', false)
            ->assertSeeText($label);
    }

    public function test_status_badge_without_value_says_unknown(): void
    {
        $this->blade('<x-status-badge :status="null" />')->assertSeeText('Unknown');
    }

    public function test_page_header_renders_title_subtitle_and_actions(): void
    {
        $this->blade('<x-page-header title="Students" subtitle="All of them"><x-slot:actions><a href="/x" class="btn">Add</a></x-slot:actions></x-page-header>')
            ->assertSee('<h1 class="page-title">Students</h1>', false)
            ->assertSeeText('All of them')
            ->assertSee('<div class="page-actions">', false);
    }

    public function test_page_header_without_actions_has_no_actions_wrapper(): void
    {
        $this->blade('<x-page-header title="Students" />')->assertDontSee('page-actions', false);
    }

    public function test_filter_bar_is_a_get_form_with_apply_and_reset(): void
    {
        $this->blade('<x-filter-bar action="/head/student"><input name="keyword"></x-filter-bar>')
            ->assertSee('method="GET" action="/head/student"', false)
            ->assertSee('<input name="keyword">', false)
            ->assertSee('<a href="/head/student" class="btn btn-outline-secondary">Reset</a>', false)
            ->assertSeeText('Apply');
    }

    public function test_empty_state_renders_icon_title_and_action(): void
    {
        $this->blade('<x-empty-state icon="people" title="No students found"><x-slot:action><a href="/r">Reset</a></x-slot:action></x-empty-state>')
            ->assertSee('<i class="bi bi-people" aria-hidden="true"></i>', false)
            ->assertSeeText('No students found')
            ->assertSee('<a href="/r">Reset</a>', false);
    }

    public function test_status_badge_without_status_prop_says_unknown(): void
    {
        $this->blade('<x-status-badge />')->assertSeeText('Unknown');
    }

    public function test_filter_bar_has_an_accessible_name(): void
    {
        $this->blade('<x-filter-bar action="/x"><input name="q"></x-filter-bar>')->assertSee('aria-label="Filter"', false);
    }

    public function test_age_counts_whole_years_from_the_date_of_birth(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-09 12:00:00'));

        $this->assertSame('25', trim((string) $this->blade('<x-age dob="2000-10-10" />')));
        $this->assertSame('26', trim((string) $this->blade('<x-age dob="2000-10-09" />')));
    }

    public function test_age_is_a_dash_when_the_date_is_missing_unreadable_or_in_the_future(): void
    {
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-09 12:00:00'));

        foreach ([null, '', 'not a date', '2030-01-01'] as $dob) {
            $this->assertSame('–', trim((string) $this->blade('<x-age :dob="$dob" />', ['dob' => $dob])), var_export($dob, true));
        }
    }
}
