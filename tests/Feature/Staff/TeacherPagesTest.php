<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class TeacherPagesTest extends StaffTestCase
{
    /** Seeded: dob 1995-04-12, percent 35, phone 081211110001, address "Jl. Melati 12". */
    private function sari(): User
    {
        return User::where('email', 'sari.teacher@gmail.com')->firstOrFail();
    }

    /** A new teacher mapped to one non-frozen and one frozen class. */
    private function teacherWithOneActiveAndOneFrozenClass(): User
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Switch Old']);
        $active = DB::table('class_transactions')->where('is_freeze', 0)->orderBy('id')->value('id');
        $frozen = DB::table('class_transactions')->where('is_freeze', 1)->orderBy('id')->value('id');
        $this->assertNotNull($active, 'seed has no active class');
        $this->assertNotNull($frozen, 'seed has no frozen class');
        DB::table('mapping_class_teachers')->insert([
            ['class_id' => $active, 'user_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()],
            ['class_id' => $frozen, 'user_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()],
        ]);

        return $teacher;
    }

    public function test_list_shows_reward_age_and_contact_but_not_the_address(): void
    {
        $this->travelTo(Carbon::parse('2026-10-09 12:00:00'));

        $html = $this->asRole('head')->get(route('head.teacher.index', ['search' => 'Sari']))->assertOk()->getContent();
        $row = $this->rowFor($html, 'Sari Wulandari');

        $this->assertStringContainsString('<td>35%</td>', $row);
        $this->assertMatchesRegularExpression('/<td>\s*31\s*<\/td>/', $row);
        $this->assertStringContainsString('<td>081211110001</td>', $row);
        $this->assertStringContainsString('<td>sari.teacher@gmail.com</td>', $row);
        $this->assertStringNotContainsString('Jl. Melati 12', $html);
    }

    public function test_row_menu_offers_replace_and_a_confirmed_delete(): void
    {
        $sari = $this->sari();
        $row = $this->rowFor($this->asRole('admin')->get(route('admin.teacher.index', ['search' => 'Sari']))->getContent(), 'Sari Wulandari');

        $this->assertStringContainsString('href="'.route('admin.teacher.edit', $sari).'"', $row);
        $this->assertStringContainsString('href="'.route('admin.teacher.switch', $sari).'"', $row);
        $this->assertStringContainsString('aria-label="More actions for Sari Wulandari"', $row);
        $this->assertStringContainsString('action="'.route('admin.teacher.destroy', $sari).'" data-confirm="Delete Sari Wulandari? This cannot be undone."', $row);
    }

    public function test_empty_search_shows_the_empty_state(): void
    {
        $this->asRole('head')->get(route('head.teacher.index', ['search' => 'zzz-nobody']))
            ->assertOk()->assertSeeText('No teachers found')->assertDontSee('<table', false);
    }

    public function test_add_page_uses_the_account_fields(): void
    {
        $this->asRole('admin')->get(route('admin.teacher.create'))->assertOk()
            ->assertSee('<h1 class="page-title">Add teacher</h1>', false)
            ->assertSee('<input type="date" id="field-inputDate_of_Birth" name="inputDate_of_Birth" value="" class="form-control" required', false)
            ->assertDontSee('name="inputBonus"', false);
    }

    public function test_update_form_has_one_address_field_and_the_reward_help(): void
    {
        $html = $this->asRole('head')->get(route('head.teacher.edit', $this->sari()))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'name="inputAddress"'));
        $this->assertStringContainsString('>Jl. Melati 12</textarea>', $html);
        $this->assertStringContainsString('Reward %', $html);
        $this->assertStringContainsString('Share of class fees used in the teacher reward report.', $html);
        $this->assertStringContainsString('name="inputBonus" value="35" class="form-control" min="0" max="100" required', $html);
        $this->assertStringContainsString('name="return_url"', $html);
    }

    public function test_posting_the_rendered_update_form_unchanged_keeps_every_column(): void
    {
        $sari = $this->sari();
        $before = (array) DB::table('users')->where('id', $sari->id)->first();

        $update = route('head.teacher.update', $sari);
        $html = $this->asRole('head')->get(route('head.teacher.edit', $sari))->assertOk()->getContent();
        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();

        $after = (array) DB::table('users')->where('id', $sari->id)->first();
        unset($before['updated_at'], $after['updated_at']);
        $this->assertEquals($before, $after);
    }

    public function test_a_null_reward_is_shown_as_zero_and_the_form_still_saves(): void
    {
        $sari = $this->sari();
        DB::table('users')->where('id', $sari->id)->update(['percent' => null]);

        $update = route('head.teacher.update', $sari);
        $html = $this->asRole('head')->get(route('head.teacher.edit', $sari))->assertOk()->getContent();
        $this->assertStringContainsString('name="inputBonus" value="0"', $html);

        $this->post($update, $this->formFields($html, $update))->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(0, (int) DB::table('users')->where('id', $sari->id)->value('percent'));
    }

    public function test_replace_page_warns_with_the_number_of_classes_replace_moves(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();

        $this->asRole('head')->get(route('head.teacher.switch', $old))->assertOk()
            ->assertSee('<h1 class="page-title">Replace Switch Old</h1>', false)
            ->assertSeeText("The 2 classes of Switch Old (including 1 frozen) move to the teacher you choose, then Switch Old's account is deleted. This cannot be undone from the app.", false);
    }

    public function test_each_candidate_row_confirms_the_move_and_the_delete(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();
        $sari = $this->sari();

        $html = $this->asRole('head')->get(route('head.teacher.switch', ['teacher' => $old, 'search' => 'Sari']))->assertOk()->getContent();
        $row = $this->rowFor($html, 'Sari Wulandari');

        $this->assertStringContainsString('action="'.route('head.teacher.replace', ['teacher' => $old, 'replacement' => $sari->id]).'"', $row);
        $this->assertStringContainsString('data-confirm="'.e("Move 2 classes from Switch Old to Sari Wulandari and delete Switch Old's account?").'"', $row);
        $this->assertStringContainsString('Replace with this teacher…', $row);
        $this->assertStringNotContainsString('Switch Old</td>', $html); // the teacher being replaced is not a candidate
    }

    public function test_replace_moves_exactly_the_counted_classes_and_deletes_the_old_account(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();
        $sari = $this->sari();
        $count = $this->asRole('head')->get(route('head.teacher.switch', $old))->viewData('classCount');
        $sariBefore = DB::table('mapping_class_teachers')->where('user_id', $sari->id)->count();

        $this->post(route('head.teacher.replace', ['teacher' => $old, 'replacement' => $sari->id]))
            ->assertRedirect(route('head.teacher.index'));

        $this->assertSame(2, $count);
        $this->assertSame($sariBefore + $count, DB::table('mapping_class_teachers')->where('user_id', $sari->id)->count());
        $this->assertSame(0, DB::table('mapping_class_teachers')->where('user_id', $old->id)->count());
        $this->assertNull($old->fresh());
    }

    public function test_replace_moves_a_frozen_class_mapping_too(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();
        $sari = $this->sari();
        $frozen = DB::table('mapping_class_teachers as mct')
            ->join('class_transactions as ct', 'ct.id', 'mct.class_id')
            ->where('mct.user_id', $old->id)->where('ct.is_freeze', 1)->value('mct.class_id');

        $this->asRole('head')->post(route('head.teacher.replace', ['teacher' => $old, 'replacement' => $sari->id]))
            ->assertRedirect(route('head.teacher.index'));

        $this->assertNotNull($frozen);
        $this->assertTrue(DB::table('mapping_class_teachers')->where('class_id', $frozen)->where('user_id', $sari->id)->exists());
        $this->assertFalse(DB::table('mapping_class_teachers')->where('class_id', $frozen)->where('user_id', $old->id)->exists());
    }

    public function test_deleting_a_teacher_whose_only_class_is_frozen_goes_to_the_switch_page(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Frozen Only']);
        $frozen = DB::table('class_transactions')->where('is_freeze', 1)->orderBy('id')->value('id');
        DB::table('mapping_class_teachers')->insert(['class_id' => $frozen, 'user_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()]);

        $this->asRole('head')->post(route('head.teacher.destroy', $teacher))
            ->assertRedirect(route('head.teacher.switch', $teacher));

        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
        $this->assertSame(1, DB::table('mapping_class_teachers')->where('user_id', $teacher->id)->count());
    }

    public function test_replace_page_for_a_teacher_without_classes_says_only_the_account_is_deleted(): void
    {
        $lonely = User::factory()->create(['role' => 'teacher', 'name' => 'Lonely Teacher']);

        $this->asRole('admin')->get(route('admin.teacher.switch', ['teacher' => $lonely, 'search' => 'Sari']))->assertOk()
            ->assertSeeText("Lonely Teacher has no classes. Choosing a teacher below only deletes Lonely Teacher's account.", false)
            ->assertSee('data-confirm="'.e("Delete Lonely Teacher's account? It has no classes to move.").'"', false);
    }

    public function test_replace_page_counts_frozen_classes_among_the_classes_that_move(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();

        $html = $this->asRole('head')->get(route('head.teacher.switch', ['teacher' => $old, 'search' => 'Sari']))->assertOk()->getContent();

        $this->assertSame(1, $this->asRole('head')->get(route('head.teacher.switch', $old))->viewData('frozenClassCount'));
        $this->assertStringNotContainsString('stays assigned', $html);
        $this->assertStringNotContainsString('keeps no teacher', $html);
    }

    public function test_frozen_copy_is_pluralised_for_several_frozen_classes(): void
    {
        $old = $this->teacherWithOneActiveAndOneFrozenClass();
        $secondFrozen = DB::table('class_transactions')->where('is_freeze', 0)->orderByDesc('id')->value('id');
        DB::table('class_transactions')->where('id', $secondFrozen)->update(['is_freeze' => 1]);
        DB::table('mapping_class_teachers')->insert(['class_id' => $secondFrozen, 'user_id' => $old->id, 'created_at' => now(), 'updated_at' => now()]);

        $html = $this->asRole('head')->get(route('head.teacher.switch', ['teacher' => $old, 'search' => 'Sari']))->assertOk()->getContent();

        $this->assertStringContainsString("The 3 classes of Switch Old (including 2 frozen) move to the teacher you choose, then Switch Old's account is deleted.", preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES)));
        $this->assertStringContainsString('data-confirm="'.e("Move 3 classes from Switch Old to Sari Wulandari and delete Switch Old's account?").'"', $this->rowFor($html, 'Sari Wulandari'));
    }

    public function test_frozen_copy_is_absent_without_frozen_classes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'name' => 'Active Only']);
        $active = DB::table('class_transactions')->where('is_freeze', 0)->orderBy('id')->value('id');
        DB::table('mapping_class_teachers')->insert(['class_id' => $active, 'user_id' => $teacher->id, 'created_at' => now(), 'updated_at' => now()]);

        $res = $this->asRole('head')->get(route('head.teacher.switch', ['teacher' => $teacher, 'search' => 'Sari']))->assertOk();
        $html = $res->getContent();

        $this->assertSame(0, $res->viewData('frozenClassCount'));
        $this->assertStringContainsString("The 1 class of Active Only moves to the teacher you choose, then Active Only's account is deleted.", preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES)));
        $this->assertStringNotContainsString('frozen', $html);
    }
}
