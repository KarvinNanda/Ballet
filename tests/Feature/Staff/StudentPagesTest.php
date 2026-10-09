<?php

namespace Tests\Feature\Staff;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentPagesTest extends StaffTestCase
{
    public function test_list_shows_twenty_rows_per_page(): void
    {
        Student::factory()->count(21)->create(['LongName' => 'Pagekid']);
        $students = $this->asRole('admin')->get(route('admin.student.index', ['keyword' => 'Pagekid']))->assertOk()->viewData('students');

        $this->assertSame(20, $students->perPage());
        $this->assertCount(20, $students->items());
    }

    public function test_trial_filter_shows_only_trial_students(): void
    {
        Student::factory()->create(['Status' => 'trial', 'LongName' => 'Filterkid Trial']);
        Student::factory()->create(['Status' => 'aktif', 'LongName' => 'Filterkid Active']);

        $this->asRole('head')->get(route('head.student.index', ['status' => 'trial', 'keyword' => 'Filterkid']))
            ->assertSee('Filterkid Trial')->assertDontSee('Filterkid Active')
            ->assertSee('aria-current="page">Trial</a>', false);
    }

    public function test_sort_keeps_keyword_and_status(): void
    {
        Student::factory()->create(['Status' => 'trial', 'LongName' => 'Sortkid Trial']);
        Student::factory()->create(['Status' => 'aktif', 'LongName' => 'Sortkid Active']);

        $this->asRole('admin')->get(route('admin.student.sort', ['column' => 'name', 'direction' => 'asc', 'keyword' => 'Sortkid', 'status' => 'trial']))
            ->assertOk()->assertSee('Sortkid Trial')->assertDontSee('Sortkid Active');
    }

    public function test_sort_links_carry_the_filters(): void
    {
        // The table (and its sort links) only renders when the filters match a row.
        Student::factory()->create(['Status' => 'trial', 'LongName' => 'Ani Linkkid']);

        $this->asRole('head')->get(route('head.student.index', ['keyword' => 'Ani', 'status' => 'trial']))
            ->assertSee('/head/student/sorting/name/asc?keyword=Ani&status=trial');
    }

    public function test_sort_ignores_an_unknown_status(): void
    {
        $this->asRole('head')->get(route('head.student.sort', ['column' => 'name', 'direction' => 'asc', 'status' => 'nope']))->assertOk();
    }

    public function test_empty_result_shows_empty_state(): void
    {
        $this->asRole('admin')->get(route('admin.student.index', ['keyword' => 'zzz-no-such-student']))
            ->assertSeeText('No students found')->assertDontSee('<table', false);
    }

    public function test_row_menu_offers_other_statuses_and_a_confirmed_delete(): void
    {
        $s = Student::factory()->create(['Status' => 'aktif', 'LongName' => 'Menukid One']);

        $this->asRole('head')->get(route('head.student.index', ['keyword' => 'Menukid One']))
            ->assertSee('value="Inactive"', false)->assertSee('value="Trial"', false)->assertDontSee('value="Active"', false)
            ->assertSee('data-confirm="Delete Menukid One? This cannot be undone."', false)
            ->assertSee('aria-label="More actions for Menukid One"', false);
    }

    public function test_row_menu_posts_to_its_own_student(): void
    {
        $a = Student::factory()->create(['LongName' => 'Rowkid A']);
        $b = Student::factory()->create(['LongName' => 'Rowkid B']);
        $html = $this->asRole('admin')->get(route('admin.student.index', ['keyword' => 'Rowkid']))->getContent();

        foreach ([$a, $b] as $s) {
            $row = $this->rowFor($html, $s->LongName);
            $this->assertStringContainsString('action="'.route('admin.student.destroy', $s->id).'"', $row);
            $this->assertStringContainsString('action="'.route('admin.student.status', $s->id).'"', $row);
            $this->assertStringContainsString('href="'.route('admin.student.show', $s->id).'"', $row);
        }
    }

    public function test_null_birthday_renders_a_dash(): void
    {
        Student::factory()->create(['LongName' => 'Nodob Kid', 'Dob' => null]);
        $row = $this->rowFor($this->asRole('head')->get(route('head.student.index', ['keyword' => 'Nodob Kid']))->getContent(), 'Nodob Kid');

        $this->assertStringNotContainsString(now()->format('d M'), $row);
        $this->assertMatchesRegularExpression('/<td>\s*-\s*<\/td>/', $row);
    }

    public function test_list_shows_nis_under_the_name(): void
    {
        Student::factory()->create(['LongName' => 'Niskid', 'nis' => 'NIS-777']);
        $this->asRole('admin')->get(route('admin.student.index', ['keyword' => 'Niskid']))->assertSeeText('NIS NIS-777');
    }

    public function test_profile_tab_is_active_by_default(): void
    {
        $s = Student::factory()->create();
        $this->asRole('admin')->get(route('admin.student.show', $s))->assertOk()
            ->assertSee('class="tab-pane fade show active" id="pane-profile"', false);
    }

    public function test_tab_query_opens_that_tab(): void
    {
        $s = Student::factory()->create();
        $this->asRole('head')->get(route('head.student.show', ['student' => $s, 'tab' => 'transactions']))
            ->assertSee('class="tab-pane fade show active" id="pane-transactions"', false)
            ->assertSee('class="tab-pane fade" id="pane-profile"', false);
    }

    public function test_unknown_tab_falls_back_to_profile(): void
    {
        $s = Student::factory()->create();
        $this->asRole('head')->get(route('head.student.show', ['student' => $s, 'tab' => '"><script>']))
            ->assertSee('class="tab-pane fade show active" id="pane-profile"', false);
    }

    public function test_validation_error_opens_profile_tab(): void
    {
        $s = Student::factory()->create();
        // Same shape a failed POST leaves in the session. With the json session serialization the bag is stored as
        // arrays; a ViewErrorBag object is dropped when the session starts, and withViewErrors() is overwritten by ShareErrorsFromSession.
        $errors = ['default' => ['messages' => ['Email' => ['bad']], 'format' => ':message']];
        $this->asRole('head')->withSession(['errors' => $errors])
            ->get(route('head.student.show', ['student' => $s, 'tab' => 'classes']))
            ->assertSee('class="tab-pane fade show active" id="pane-profile"', false);
    }

    public function test_flash_error_opens_profile_tab(): void
    {
        $s = Student::factory()->create();
        $this->asRole('head')->withSession(['error' => 'Quota sudah berubah'])
            ->get(route('head.student.show', ['student' => $s, 'tab' => 'transactions']))
            ->assertSee('class="tab-pane fade show active" id="pane-profile"', false);
    }

    public function test_stale_quota_refusal_reopens_profile_with_submitted_values_and_fresh_quota_original(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Stale Bank']);
        DB::table('rekenings')->insert(['bank_rek' => '5553334444', 'nama_pengirim' => 'Stale Parent', 'banks_id' => $bank]);
        $s = Student::factory()->create(['bank_rek' => '5553334444', 'Dob' => '2016-03-04', 'EnrollDate' => '2024-01-10', 'Quota' => 5]);

        $update = route('head.student.update', $s);
        $html = $this->asRole('head')->get(route('head.student.show', $s))->assertOk()->getContent();
        $fields = $this->formFields($html, $update);
        $this->assertSame('5', $fields['Quota_original']);

        DB::table('students')->where('id', $s->id)->increment('Quota'); // attendance while the form was open

        $this->from(route('head.student.show', ['student' => $s, 'tab' => 'transactions']))->followingRedirects()
            ->post($update, array_merge($fields, ['Quota' => 3, 'Phone1' => '081299990000']))
            ->assertOk()
            ->assertSee('class="tab-pane fade show active" id="pane-profile"', false)
            ->assertSee('id="field-Quota" name="Quota" value="3"', false)
            ->assertSee('id="field-Phone1" name="Phone1" value="081299990000"', false)
            ->assertSee('name="Quota_original" value="6"', false)
            ->assertSee('Quota sudah berubah');

        $this->assertSame(6, (int) DB::table('students')->where('id', $s->id)->value('Quota'));
    }

    public function test_validation_error_round_trip_keeps_an_attendance_driven_quota(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Race Bank']);
        DB::table('rekenings')->insert(['bank_rek' => '5557778888', 'nama_pengirim' => 'Race Parent', 'banks_id' => $bank]);
        $s = Student::factory()->create(['bank_rek' => '5557778888', 'Dob' => '2016-03-04', 'EnrollDate' => '2024-01-10', 'Quota' => 5]);

        $update = route('head.student.update', $s);
        $show = route('head.student.show', $s);
        $fields = $this->formFields($this->asRole('head')->get($show)->assertOk()->getContent(), $update);

        DB::table('students')->where('id', $s->id)->increment('Quota'); // attendance while the form was open

        $errorPage = $this->from($show)->followingRedirects()
            ->post($update, array_merge($fields, ['Email' => 'not-an-email']))
            ->assertOk()->getContent();
        $reopened = $this->formFields($errorPage, $update);

        $this->post($update, array_merge($reopened, ['Email' => 'fixed@example.com']))
            ->assertSessionHasNoErrors()->assertSessionMissing('error')->assertRedirect();

        $this->assertSame(6, (int) DB::table('students')->where('id', $s->id)->value('Quota'));
        $this->assertSame('fixed@example.com', DB::table('students')->where('id', $s->id)->value('Email'));
    }

    public function test_dates_are_date_inputs_with_iso_values(): void
    {
        $s = Student::factory()->create(['Dob' => '2016-03-04', 'EnrollDate' => '2024-01-10']);
        $this->asRole('admin')->get(route('admin.student.show', $s))
            ->assertSee('<input type="date" id="field-dob" name="dob" value="2016-03-04"', false)
            ->assertSee('<input type="date" id="field-EnrollDate" name="EnrollDate" value="2024-01-10"', false);
    }

    public function test_summary_shows_status_quota_and_unpaid_count(): void
    {
        $s = Student::factory()->create(['Status' => 'trial', 'Quota' => 7]);
        DB::table('transactions')->insert([
            ['students_id' => $s->id, 'payment_status' => 'Unpaid', 'price' => 100, 'transaction_date' => '2026-09-01'],
            ['students_id' => $s->id, 'payment_status' => 'Unpaid', 'price' => 100, 'transaction_date' => '2026-10-01'],
            ['students_id' => $s->id, 'payment_status' => 'Paid', 'price' => 100, 'transaction_date' => '2026-08-01'],
        ]);

        $this->asRole('head')->get(route('head.student.show', $s))
            ->assertSee('status-badge-warning', false)
            ->assertSee('<span class="stat-label">Quota</span><span class="stat-value">7</span>', false)
            ->assertSee('<span class="stat-label">Classes</span><span class="stat-value">0</span>', false)
            ->assertSee('<span class="stat-label">Unpaid</span><span class="stat-value">2</span>', false)
            ->assertSee('Transactions (3)');
    }

    public function test_detail_form_has_every_field_the_update_request_reads(): void
    {
        $s = Student::factory()->create();
        $response = $this->asRole('admin')->get(route('admin.student.show', $s));
        $rules = array_keys((new \App\Http\Requests\Staff\UpdateStudentRequest)->rules());

        foreach (array_diff($rules, ['MaxQuota']) as $field) { // MaxQuota is not on the form on purpose
            $response->assertSee('name="'.$field.'"', false);
        }
    }

    public static function roundTripStudents(): array
    {
        return [
            'optional fields filled' => [['nis' => 'RT-1', 'ShortName' => 'Rt', 'Phone2' => '081200000001', 'Instagram' => '@rt', 'Line' => 'rtline', 'is_new' => 1, 'Status' => 'trial', 'Quota' => 4]],
            'optional fields empty' => [['nis' => null, 'ShortName' => null, 'Phone2' => null, 'Instagram' => null, 'Line' => null, 'is_new' => 0, 'Status' => 'aktif', 'Quota' => 0, 'EnrollDate' => null]],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('roundTripStudents')]
    public function test_posting_the_rendered_detail_form_unchanged_keeps_every_column(array $attributes): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Roundtrip Bank']);
        DB::table('rekenings')->insert(['bank_rek' => '5551112222', 'nama_pengirim' => 'Round Parent', 'banks_id' => $bank]);
        $s = Student::factory()->create(array_merge(['bank_rek' => '5551112222', 'Dob' => '2016-03-04', 'EnrollDate' => '2024-01-10'], $attributes));
        $before = (array) DB::table('students')->where('id', $s->id)->first();

        $update = route('head.student.update', $s);
        $html = $this->asRole('head')->get(route('head.student.show', $s))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertSessionMissing('error')->assertRedirect();

        $after = (array) DB::table('students')->where('id', $s->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertSame($before, $after);
        $this->assertSame('Round Parent', DB::table('rekenings')->where('bank_rek', '5551112222')->value('nama_pengirim'));
        $this->assertSame($bank, (int) DB::table('rekenings')->where('bank_rek', '5551112222')->value('banks_id'));
    }

    private function newStudent(array $override = []): array
    {
        return array_merge([
            'inputLongName' => 'Terms Kid', 'inputNickName' => 'Tk', 'inputParentName' => 'Ibu Tk', 'inputCity' => 'Jakarta',
            'inputEmail' => 'termskid@example.com', 'inputDate_of_Birth' => '2015-02-02', 'inputAddress' => 'Jl. C',
            'inputPhone1' => '081234567890', 'inputWhatsapp' => '081234567890', 'inputPostalCode' => '12345',
            'terms_accepted' => '1',
        ], $override);
    }

    public function test_store_without_terms_confirmation_is_refused(): void
    {
        $payload = $this->newStudent();
        unset($payload['terms_accepted']);

        $this->asRole('admin')->post(route('admin.student.store'), $payload)
            ->assertSessionHasErrors(['terms_accepted' => 'You must confirm the terms and conditions.']);
        $this->assertFalse(Student::where('Email', 'termskid@example.com')->exists());
    }

    public function test_store_with_terms_confirmation_creates_the_student(): void
    {
        $this->asRole('head')->post(route('head.student.store'), $this->newStudent())->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(Student::where('Email', 'termskid@example.com')->exists());
    }

    public function test_create_page_has_visible_submit_and_required_terms_checkbox(): void
    {
        $this->asRole('admin')->get(route('admin.student.create'))->assertOk()
            ->assertSee('<input class="form-check-input" type="checkbox" name="terms_accepted" value="1" id="field-terms_accepted" required', false)
            ->assertSee('<button type="submit" class="btn btn-primary">Create student</button>', false)
            ->assertSee('data-bs-target="#termsModal"', false)
            ->assertDontSee('id="submit"', false);
    }

    public function test_create_form_has_every_field_the_store_request_reads(): void
    {
        $response = $this->asRole('head')->get(route('head.student.create'));
        foreach (array_keys((new \App\Http\Requests\Staff\StoreStudentRequest)->rules()) as $field) {
            $response->assertSee('name="'.$field.'"', false);
        }
    }

    public function test_add_class_page_has_header_back_link_and_add_buttons(): void
    {
        [$student] = $this->studentAndFreeClass();
        $html = $this->asRole('head')->get(route('head.student.class.create', $student))->assertOk()
            ->assertSee('<h1 class="page-title">Add class for '.e($student->LongName).'</h1>', false)
            ->assertSee('href="'.route('head.student.show', ['student' => $student->id, 'tab' => 'classes']).'"', false)
            ->assertDontSee('<div class="container">', false)
            ->getContent();

        // Every class the page offers posts to this student.
        preg_match_all('#action="[^"]*/student/class/add/(\d+)/(\d+)"#', $html, $forms, PREG_SET_ORDER);
        $this->assertNotEmpty($forms, 'no class offered');
        foreach ($forms as [, , $studentId]) {
            $this->assertSame((string) $student->id, $studentId);
        }
    }

    public function test_transactions_tab_survives_an_invalid_stored_discount(): void
    {
        $s = Student::factory()->create();
        DB::table('transactions')->insert([
            ['students_id' => $s->id, 'payment_status' => 'Unpaid', 'price' => 350000, 'discount' => 'abc', 'transaction_date' => '2026-09-01'],
            ['students_id' => $s->id, 'payment_status' => 'Unpaid', 'price' => 350000, 'discount' => '10%', 'transaction_date' => '2026-10-01'],
        ]);

        $this->asRole('head')->get(route('head.student.show', ['student' => $s, 'tab' => 'transactions']))
            ->assertOk()
            ->assertSeeText('Invalid discount')
            ->assertSeeText('Rp315,000');
    }
}
