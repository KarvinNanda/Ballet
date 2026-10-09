<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class FormKitTest extends TestCase
{
    public function test_field_renders_label_input_and_value(): void
    {
        $this->blade('<x-form.field name="LongName" label="Long name" value="Alya" required />')
            ->assertSee('<label for="field-LongName" class="form-label">Long name', false)
            ->assertSee('<input type="text" id="field-LongName" name="LongName" value="Alya" class="form-control" required', false)
            ->assertDontSee('is-invalid', false);
    }

    public function test_field_shows_error_under_the_control(): void
    {
        $this->withViewErrors(['Email' => 'The email field must be a valid email address.'])
            ->blade('<x-form.field name="Email" label="Email" type="email" value="x" />')
            ->assertSee('class="form-control is-invalid"', false)
            ->assertSee('aria-describedby="field-Email-error"', false)
            ->assertSee('<div id="field-Email-error" class="invalid-feedback d-block">The email field must be a valid email address.</div>', false);
    }

    public function test_field_help_is_linked_with_aria_describedby(): void
    {
        $this->blade('<x-form.field name="nis" label="NIS" help="Optional" />')
            ->assertSee('aria-describedby="field-nis-help"', false)
            ->assertSee('<div id="field-nis-help" class="form-text">Optional</div>', false);
    }

    public function test_select_marks_the_current_value(): void
    {
        $this->blade('<x-form.field name="status" label="Status" type="select" :options="[\'aktif\' => \'Active\', \'trial\' => \'Trial\']" value="trial" required />')
            ->assertSee('<select id="field-status" name="status" class="form-select" required', false)
            ->assertSee('<option value="trial" selected>Trial</option>', false)
            ->assertSee('<option value="aktif" >Active</option>', false);
    }

    public function test_extra_attributes_reach_the_control_not_the_wrapper(): void
    {
        $this->blade('<x-form.field name="Quota" label="Quota" type="number" min="0" />')
            ->assertSee('min="0"', false)
            ->assertSee('<div class="form-field">', false)
            ->assertSee('<input type="number" id="field-Quota" name="Quota" value="" class="form-control" min="0"', false);
    }

    public function test_wide_adds_the_wide_class_to_the_wrapper(): void
    {
        $this->blade('<x-form.field name="Address" label="Address" wide />')
            ->assertSee('<div class="form-field form-field-wide">', false);
    }

    public function test_class_attribute_lands_on_the_control(): void
    {
        $this->blade('<x-form.field name="Line" label="Line" class="x" />')
            ->assertSee('<div class="form-field">', false)
            ->assertSee('class="form-control x"', false);
    }

    public function test_select_without_a_current_value_starts_on_a_blank_option(): void
    {
        $this->blade('<x-form.field name="status" label="Status" type="select" :options="[\'aktif\' => \'Active\']" />')
            ->assertSee('<option value="" selected>Select', false)
            ->assertDontSee('<option value="aktif" selected', false);
    }

    public function test_select_with_a_value_outside_its_options_keeps_that_value(): void
    {
        $this->blade('<x-form.field name="status" label="Status" type="select" :options="[\'aktif\' => \'Active\']" value="legacy" />')
            ->assertSee('<option value="legacy" selected>legacy</option>', false)
            ->assertDontSee('<option value="aktif" selected', false);
    }

    public function test_date_input_formats_a_datetime_value(): void
    {
        $this->blade('<x-form.field name="dob" label="Dob" type="date" value="2026-10-09 14:30:00" />')
            ->assertSee('value="2026-10-09"', false)
            ->assertDontSee('14:30', false);
    }

    public function test_date_input_formats_an_iso_t_datetime_value(): void
    {
        $this->blade('<x-form.field name="dob" label="Dob" type="date" value="2026-10-09T14:30:00" />')
            ->assertSee('value="2026-10-09"', false)
            ->assertDontSee('14:30', false);
    }

    public function test_date_input_shows_rejected_old_input_unchanged(): void
    {
        // old() reads from the current request's session store, so bind one with flashed input.
        $request = request();
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('_old_input', ['dob' => '20166-03-04']);

        $this->blade('<x-form.field name="dob" label="Dob" type="date" />')
            ->assertSee('value="20166-03-04"', false)
            ->assertDontSee('value="2006-03-04"', false);
    }

    public function test_field_does_not_break_on_crafted_array_old_input(): void
    {
        $request = request();
        $request->setLaravelSession($this->app['session.store']);
        $request->session()->put('_old_input', ['Email' => ['x']]);

        $this->blade('<x-form.field name="Email" label="Email" type="email" />')
            ->assertSee('value=""', false)
            ->assertDontSee('Array', false);
    }

    public function test_date_input_does_not_roll_over_an_impossible_date(): void
    {
        $this->blade('<x-form.field name="dob" label="Dob" type="date" value="2026-02-30" />')
            ->assertSee('value="2026-02-30"', false)
            ->assertDontSee('2026-03-02', false);
    }

    public function test_date_input_formats_a_carbon_value(): void
    {
        $this->blade('<x-form.field name="dob" label="DOB" type="date" :value="$d" />', ['d' => \Illuminate\Support\Carbon::create(2026, 10, 9, 14, 30)])
            ->assertSee('value="2026-10-09"', false)
            ->assertDontSee('14:30', false);
    }

    public function test_textarea_holds_the_value(): void
    {
        $this->blade('<x-form.field name="Address" label="Address" type="textarea" value="Jl. Mawar 1" />')
            ->assertSee('<textarea id="field-Address" name="Address" class="form-control" rows="3"', false)
            ->assertSee('>Jl. Mawar 1</textarea>', false);
    }

    public function test_value_is_escaped(): void
    {
        $this->blade('<x-form.field name="Line" label="Line" :value="$v" />', ['v' => '"><script>x</script>'])
            ->assertDontSee('<script>x</script>', false)
            ->assertSee('value="&quot;&gt;&lt;script&gt;x&lt;/script&gt;"', false);
    }

    public function test_section_wraps_fields_in_a_grid(): void
    {
        $this->blade('<x-form.section title="Identity"><span>f</span></x-form.section>')
            ->assertSee('<legend class="form-section-title">Identity</legend>', false)
            ->assertSee('<div class="form-grid">', false);
    }

    public function test_confirm_form_posts_with_csrf_and_message(): void
    {
        $this->blade('<x-confirm-form action="/head/student/delete/1" message="Delete Alya?"><button>Delete</button></x-confirm-form>')
            ->assertSee('<form method="POST" action="/head/student/delete/1" data-confirm="Delete Alya?"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('<button>Delete</button>', false);
    }

    public function test_invalid_control_is_marked_aria_invalid(): void
    {
        $this->withViewErrors(['Email' => 'bad'])
            ->blade('<x-form.field name="Email" label="Email" />')
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_valid_control_has_no_aria_invalid(): void
    {
        $this->blade('<x-form.field name="Email" label="Email" />')->assertDontSee('aria-invalid', false);
    }

    public function test_invalid_select_is_marked_aria_invalid(): void
    {
        $this->withViewErrors(['status' => 'bad'])
            ->blade('<x-form.field name="status" label="S" type="select" :options="[\'a\' => \'A\']" />')
            ->assertSee('<select id="field-status" name="status" class="form-select is-invalid"', false)
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_invalid_textarea_is_marked_aria_invalid(): void
    {
        $this->withViewErrors(['Address' => 'bad'])
            ->blade('<x-form.field name="Address" label="A" type="textarea" />')
            ->assertSee('<textarea id="field-Address" name="Address" class="form-control is-invalid"', false)
            ->assertSee('aria-invalid="true"', false);
    }

    public function test_account_fields_render_the_stored_values_once(): void
    {
        $account = new \App\Models\User(['name' => 'Sari', 'email' => 'sari@example.com']);
        $account->forceFill(['dob' => '1995-04-12', 'phone' => '081211110001', 'address' => 'Jl. Melati 12']);

        $html = (string) $this->blade('<x-form.account-fields :account="$a"><p>extra</p></x-form.account-fields>', ['a' => $account]);

        $this->assertStringContainsString('name="inputName" value="Sari"', $html);
        $this->assertStringContainsString('<input type="email" id="field-inputEmail" name="inputEmail" value="sari@example.com"', $html);
        $this->assertStringContainsString('name="inputDate_of_Birth" value="1995-04-12"', $html);
        $this->assertStringContainsString('<input type="tel" id="field-inputPhone" name="inputPhone" value="081211110001"', $html);
        $this->assertSame(1, substr_count($html, 'name="inputAddress"'));
        $this->assertStringContainsString('>Jl. Melati 12</textarea>', $html);
        $this->assertStringContainsString('<p>extra</p>', $html);
    }

    public function test_account_fields_are_empty_on_add(): void
    {
        $this->blade('<x-form.account-fields />')
            ->assertSee('<input type="email" id="field-inputEmail" name="inputEmail" value="" class="form-control" required', false)
            ->assertSee('<textarea id="field-inputAddress" name="inputAddress" class="form-control" rows="3" required', false);
    }
}
