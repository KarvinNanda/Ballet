<?php

namespace Tests\Feature\Staff;

use App\Models\Student;
use Illuminate\Support\Facades\DB;

class StudentTest extends StaffTestCase
{
    /** Every field the detail form posts; values are the student's current ones unless overridden. */
    private function updatePayload(Student $s, array $override = []): array
    {
        $rek = DB::table('rekenings')
            ->leftJoin('banks', 'banks.id', 'rekenings.banks_id')
            ->where('rekenings.bank_rek', $s->bank_rek)
            ->first(['rekenings.nama_pengirim', 'banks.bank_name']);

        return array_merge([
            'nis' => $s->nis, 'LongName' => $s->LongName, 'ShortName' => $s->ShortName,
            'Email' => $s->Email, 'dob' => $s->Dob, 'Address' => $s->Address,
            'nama_orang_tua' => $s->nama_orang_tua, 'city' => $s->City, 'kode_pos' => $s->kode_pos,
            'Phone1' => $s->Phone1, 'Phone2' => $s->Phone2, 'Whatsapp' => $s->Whatsapp,
            'Instagram' => $s->Instagram, 'Line' => $s->Line, 'EnrollDate' => $s->EnrollDate,
            'Quota' => $s->Quota ?? 0, 'Quota_original' => $s->Quota ?? 0, 'MaxQuota' => 12, 'is_new' => 'No', 'status' => $s->Status,
            'bank' => $rek->bank_name ?? 'BCA', 'accountno' => $s->bank_rek ?? '1234567890',
            'sender' => $rek->nama_pengirim ?? 'Parent',
        ], $override);
    }

    public function test_admin_update_keeps_and_sets_max_quota(): void // was NULL
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        $this->asRole('admin')->post(route('admin.student.update', $s), $this->updatePayload($s))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(12, (int) $s->fresh()->MaxQuota);
    }

    public function test_update_without_max_quota_keeps_the_old_value(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        DB::table('students')->where('id', $s->id)->update(['MaxQuota' => 7]);
        $this->asRole('head')->post(route('head.student.update', $s), $this->updatePayload($s, ['MaxQuota' => '']))->assertSessionHasNoErrors();
        $this->assertSame(7, (int) $s->fresh()->MaxQuota);
    }

    public function test_admin_can_edit_bank_and_sender(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        $this->asRole('admin')->post(route('admin.student.update', $s), $this->updatePayload($s, ['sender' => 'New Sender']))->assertSessionHasNoErrors();
        $this->assertSame('New Sender', DB::table('rekenings')->where('bank_rek', $s->fresh()->bank_rek)->value('nama_pengirim'));
    }

    public function test_changing_account_number_does_not_touch_a_sibling_sharing_the_old_one(): void
    {
        $a = Student::factory()->create(['bank_rek' => '5550001111']);
        $b = Student::factory()->create(['bank_rek' => '5550001111']);
        DB::table('rekenings')->insert(['bank_rek' => '5550001111', 'nama_pengirim' => 'Shared Parent']);

        $this->asRole('head')->post(route('head.student.update', $a), $this->updatePayload($a, ['accountno' => '5559998888', 'sender' => 'Other Parent', 'bank' => 'BCA']))
            ->assertSessionHasNoErrors();

        $this->assertSame('5559998888', $a->fresh()->bank_rek);
        $this->assertSame('5550001111', $b->fresh()->bank_rek);
        $this->assertSame('Shared Parent', DB::table('rekenings')->where('bank_rek', '5550001111')->value('nama_pengirim'));
        $this->assertSame('Other Parent', DB::table('rekenings')->where('bank_rek', '5559998888')->value('nama_pengirim'));
    }

    public function test_moving_onto_another_students_account_with_a_different_sender_is_refused(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Keep Bank']);
        $c = Student::factory()->create(['bank_rek' => '5550006666']);
        $a = Student::factory()->create(['bank_rek' => '5550007777']);
        DB::table('rekenings')->insert([
            ['bank_rek' => '5550006666', 'nama_pengirim' => 'C Parent', 'banks_id' => $bank],
            ['bank_rek' => '5550007777', 'nama_pengirim' => 'A Parent', 'banks_id' => null],
        ]);

        $this->asRole('head')->post(route('head.student.update', $a), $this->updatePayload($a, ['accountno' => '5550006666', 'sender' => 'Different', 'bank' => 'Keep Bank']))
            ->assertSessionHas('error');

        $row = DB::table('rekenings')->where('bank_rek', '5550006666')->first();
        $this->assertSame('C Parent', $row->nama_pengirim);
        $this->assertSame($bank, (int) $row->banks_id);
        $this->assertSame('5550007777', $a->fresh()->bank_rek);
    }

    public function test_moving_onto_another_students_account_with_the_same_sender_attaches_without_changes(): void
    {
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Keep Bank']);
        $c = Student::factory()->create(['bank_rek' => '5550006666']);
        $a = Student::factory()->create(['bank_rek' => '5550007777']);
        DB::table('rekenings')->insert([
            ['bank_rek' => '5550006666', 'nama_pengirim' => 'C Parent', 'banks_id' => $bank],
            ['bank_rek' => '5550007777', 'nama_pengirim' => 'A Parent', 'banks_id' => null],
        ]);

        $this->asRole('head')->post(route('head.student.update', $a), $this->updatePayload($a, ['accountno' => '5550006666', 'sender' => 'C Parent', 'bank' => 'Keep Bank']))
            ->assertSessionHasNoErrors()->assertSessionMissing('error');

        $this->assertSame('5550006666', $a->fresh()->bank_rek);
        $this->assertSame('C Parent', DB::table('rekenings')->where('bank_rek', '5550006666')->value('nama_pengirim'));
    }

    public function test_invalid_status_and_enroll_date_are_rejected(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        $this->asRole('head')->post(route('head.student.update', $s), $this->updatePayload($s, ['status' => 'banana', 'EnrollDate' => 'not-a-date']))
            ->assertSessionHasErrors(['status', 'EnrollDate']);
        $this->assertSame('aktif', $s->fresh()->Status);
    }

    public function test_store_reuses_an_existing_account_row(): void
    {
        DB::table('rekenings')->insert(['bank_rek' => '5550008888', 'nama_pengirim' => 'Sibling Parent']);
        $this->asRole('admin')->post(route('admin.student.store'), [
            'inputLongName' => 'New Kid', 'inputNickName' => 'NK', 'inputParentName' => 'P', 'inputCity' => 'Jakarta',
            'inputEmail' => 'nk@example.com', 'inputDate_of_Birth' => '2015-01-01', 'inputAddress' => 'Somewhere',
            'inputPhone1' => '081234567890', 'inputWhatsapp' => '081234567890', 'inputPostalCode' => '14480',
            'inputRekening' => '5550008888', 'inputNamaPengirim' => 'Other', 'inputBankName' => 'BCA',
            'terms_accepted' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, DB::table('rekenings')->where('bank_rek', '5550008888')->count());
        $this->assertSame('Sibling Parent', DB::table('rekenings')->where('bank_rek', '5550008888')->value('nama_pengirim'));
        $this->assertSame('5550008888', Student::where('LongName', 'New Kid')->value('bank_rek'));
    }

    public function test_changing_account_number_renames_the_row_when_no_one_else_uses_it(): void
    {
        $a = Student::factory()->create(['bank_rek' => '5550002222']);
        DB::table('rekenings')->insert(['bank_rek' => '5550002222', 'nama_pengirim' => 'Solo']);

        $this->asRole('admin')->post(route('admin.student.update', $a), $this->updatePayload($a, ['accountno' => '5550003333', 'sender' => 'Solo']))
            ->assertSessionHasNoErrors();

        $this->assertSame(0, DB::table('rekenings')->where('bank_rek', '5550002222')->count());
        $this->assertSame(1, DB::table('rekenings')->where('bank_rek', '5550003333')->count());
    }

    public function test_student_without_account_gets_one_on_update(): void
    {
        $a = Student::factory()->create(['bank_rek' => null]);
        $this->asRole('head')->post(route('head.student.update', $a), $this->updatePayload($a, ['accountno' => '5550004444', 'sender' => 'New', 'bank' => '']))
            ->assertSessionHasNoErrors();
        $this->assertSame('5550004444', $a->fresh()->bank_rek);
        $this->assertSame('New', DB::table('rekenings')->where('bank_rek', '5550004444')->value('nama_pengirim'));
    }

    public function test_admin_add_class_to_student_saves(): void // dd() before
    {
        [$student, $classId] = $this->studentAndFreeClass();
        $this->asRole('admin')->post(route('admin.student.class.store', ['class' => $classId, 'student' => $student]))->assertRedirect();
        $this->assertTrue(DB::table('mapping_class_children')->where('student_id', $student->id)->where('class_id', $classId)->exists());
        $this->assertSame(3, DB::table('transactions')->where('students_id', $student->id)->where('class_transactions_id', $classId)->count());
    }

    public function test_adding_the_same_class_twice_does_not_duplicate_transactions(): void
    {
        [$student, $classId] = $this->studentAndFreeClass();
        $url = route('head.student.class.store', ['class' => $classId, 'student' => $student]);
        $this->asRole('head')->post($url);
        $this->asRole('head')->post($url)->assertSessionHas('error');
        $this->assertSame(3, DB::table('transactions')->where('students_id', $student->id)->where('class_transactions_id', $classId)->count());
    }

    public function test_head_no_classes_redirect_stays_in_head(): void // went to an admin route
    {
        $student = Student::firstOrFail();
        DB::table('mapping_class_children')->delete(); // no class has students: nothing to offer
        $response = $this->asRole('head')->get(route('head.student.class.create', $student));
        $response->assertRedirect(route('head.student.show', $student));
        $this->assertStringStartsWith(url('/head/'), $response->headers->get('Location'));
    }

    public function test_sort_keeps_students_without_a_bank_account(): void
    {
        Student::factory()->create(['Status' => 'aktif', 'bank_rek' => null, 'LongName' => 'Zz No Bank']);
        $this->asRole('head')->get(route('head.student.sort', ['column' => 'name', 'direction' => 'desc']))
            ->assertSee('Zz No Bank');
    }

    public function test_pager_keeps_status_and_keyword(): void
    {
        Student::factory()->count(21)->create(['Status' => 'aktif', 'LongName' => 'Pagerkid']);
        $this->asRole('admin')->get(route('admin.student.index', ['status' => 'aktif', 'keyword' => 'Pagerkid']))
            ->assertSee('status=aktif', false)->assertSee('keyword=Pagerkid', false);
    }

    public function test_keyword_matches_sender_name_and_bank_name(): void
    {
        $s = Student::factory()->create(['LongName' => 'Plain Kid', 'bank_rek' => '5550005555']);
        $bank = DB::table('banks')->insertGetId(['bank_name' => 'Zeta Bank']);
        DB::table('rekenings')->insert(['bank_rek' => '5550005555', 'nama_pengirim' => 'Quentin Sender', 'banks_id' => $bank]);

        $this->asRole('admin')->get(route('admin.student.index', ['keyword' => 'Quentin']))->assertSee('Plain Kid');
        $this->asRole('head')->get(route('head.student.index', ['keyword' => 'Zeta Bank']))->assertSee('Plain Kid');
    }

    public function test_status_filter_uses_the_stored_status_values(): void
    {
        Student::factory()->create(['Status' => 'non-aktif', 'LongName' => 'Gone Kid']);
        $this->asRole('head')->get(route('head.student.inactive'))->assertRedirect(route('head.student.index', ['status' => 'non-aktif']));
        $this->asRole('head')->get(route('head.student.index', ['status' => 'non-aktif']))->assertSee('Gone Kid');
        $this->asRole('head')->get(route('head.student.index', ['status' => 'aktif']))->assertDontSee('Gone Kid');
    }

    public function test_old_admin_urls_redirect(): void
    {
        $this->asRole('admin')->get('/admin/student/view')->assertRedirect('/admin/student');
        $this->asRole('admin')->get('/admin/student/form')->assertRedirect('/admin/student/add');
    }

    public function test_unknown_student_is_404(): void
    {
        $this->asRole('admin')->post(route('admin.student.update', 999999), [])->assertNotFound();
        $this->asRole('head')->get(route('head.student.show', 999999))->assertNotFound();
    }

    private function storePayload(array $override = []): array
    {
        return array_merge([
            'inputLongName' => 'Ani Lestari', 'inputNickName' => 'Ani', 'inputParentName' => 'Ibu Ani', 'inputCity' => 'Jakarta',
            'inputEmail' => 'ani@example.com', 'inputDate_of_Birth' => '2015-02-02', 'inputAddress' => 'Jl. B',
            'inputPhone1' => '081234567890', 'inputWhatsapp' => '081234567890', 'inputPostalCode' => '12345',
            'inputNis' => 'N-1', 'inputPhone2' => '', 'inputInstagram' => '', 'inputLine' => 'aniline',
            'inputRekening' => '', 'inputBankName' => '', 'inputNamaPengirim' => '',
            'terms_accepted' => '1',
        ], $override);
    }

    public function test_line_is_saved_even_without_instagram(): void // was '-' unless Instagram was filled
    {
        $this->asRole('head')->post(route('head.student.store'), $this->storePayload())->assertSessionHasNoErrors();
        $this->assertSame('aniline', \App\Models\Student::where('Email', 'ani@example.com')->value('Line'));
    }

    public function test_long_optional_fields_are_validation_errors(): void
    {
        foreach (['inputNis', 'inputPhone2', 'inputInstagram', 'inputLine', 'inputRekening', 'inputBankName', 'inputNamaPengirim'] as $field) {
            $this->asRole('head')->post(route('head.student.store'), $this->storePayload([$field => str_repeat('x', 300)]))
                ->assertSessionHasErrors($field);
        }
    }

    public function test_toggle_status_rejects_unknown_values(): void
    {
        $student = \App\Models\Student::where('Status', 'aktif')->firstOrFail();
        $this->asRole('head')->post(route('head.student.status', $student), ['stats' => 'Banana'])->assertSessionHasErrors('stats');
        $this->assertSame('aktif', $student->fresh()->Status);
        $this->post(route('head.student.status', $student), ['stats' => 'Trial'])->assertRedirect();
        $this->assertSame('trial', $student->fresh()->Status);
    }

    /** A student whose form was opened at Quota 5, after which attendance moved the DB value to 6. */
    private function studentWithAttendanceWhileFormOpen(): array
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        DB::table('students')->where('id', $s->id)->update(['Quota' => 5]);
        $opened = $this->updatePayload($s->fresh()); // Quota and Quota_original are 5
        DB::table('students')->where('id', $s->id)->increment('Quota'); // attendance while the form is open

        return [$s, $opened];
    }

    public function test_saving_without_touching_quota_keeps_attendance_recorded_meanwhile(): void
    {
        [$s, $opened] = $this->studentWithAttendanceWhileFormOpen();

        $this->asRole('admin')->post(route('admin.student.update', $s), array_merge($opened, ['Phone1' => '081299990000']))
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(6, (int) $s->fresh()->Quota);
        $this->assertSame('081299990000', $s->fresh()->Phone1);
    }

    public function test_manual_quota_change_on_a_stale_form_is_refused_and_saves_nothing(): void
    {
        [$s, $opened] = $this->studentWithAttendanceWhileFormOpen();
        $phone = $s->fresh()->Phone1;

        $this->asRole('head')->post(route('head.student.update', $s), array_merge($opened, ['Quota' => 3, 'Phone1' => '081299990000']))
            ->assertSessionHas('error', 'Quota sudah berubah sejak halaman dibuka. Buka ulang halaman lalu coba lagi.');

        $this->assertSame(6, (int) $s->fresh()->Quota);
        $this->assertSame($phone, $s->fresh()->Phone1);
    }

    public function test_manual_quota_change_on_a_fresh_form_is_saved(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        DB::table('students')->where('id', $s->id)->update(['Quota' => 5]);

        $this->asRole('head')->post(route('head.student.update', $s), $this->updatePayload($s->fresh(), ['Quota' => 3]))
            ->assertSessionHasNoErrors()->assertRedirect();

        $this->assertSame(3, (int) $s->fresh()->Quota);
    }

    public function test_detail_form_posts_the_quota_it_was_opened_with(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        DB::table('students')->where('id', $s->id)->update(['Quota' => 5]);

        $this->asRole('admin')->get(route('admin.student.show', $s))->assertOk()
            ->assertSee('name="Quota_original" value="5"', false);
    }

    public function test_missing_quota_original_is_a_validation_error(): void
    {
        $s = Student::where('Status', 'aktif')->firstOrFail();
        $payload = $this->updatePayload($s);
        unset($payload['Quota_original']);

        $this->asRole('head')->post(route('head.student.update', $s), $payload)->assertSessionHasErrors('Quota_original');
    }
}
