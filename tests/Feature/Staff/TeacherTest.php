<?php

namespace Tests\Feature\Staff;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class TeacherTest extends StaffTestCase
{
    public function test_head_search_works_over_get(): void // was 405
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $this->asRole('head')->get(route('head.teacher.index', ['search' => $teacher->name]))
            ->assertOk()->assertSee($teacher->name);
    }

    public function test_pager_keeps_the_search_term(): void // admin lost it on page 2
    {
        User::factory()->count(6)->create(['role' => 'teacher', 'name' => 'Pagerteacher']);
        $this->asRole('admin')->get(route('admin.teacher.index', ['search' => 'Pagerteacher']))
            ->assertSee('search=Pagerteacher', false);
    }

    public function test_replace_requires_another_existing_teacher(): void
    {
        $teacher = User::where('role', 'teacher')->firstOrFail();
        $admin = $this->user('admin');

        $this->asRole('head')->post(route('head.teacher.replace', [$teacher, $teacher->id]))->assertNotFound();
        $this->post(route('head.teacher.replace', [$teacher, $admin->id]))->assertNotFound();
        $this->assertNotNull($teacher->fresh());
    }

    public function test_add_form_keeps_old_input_after_a_validation_error(): void
    {
        $this->asRole('head')->from(route('head.teacher.create'))
            ->post(route('head.teacher.store'), ['inputName' => 'Kept Name'])
            ->assertRedirect(route('head.teacher.create'));
        $this->get(route('head.teacher.create'))->assertSee('Kept Name');
    }

    public function test_routes_only_accept_teacher_accounts(): void
    {
        $this->asRole('admin')->get(route('admin.teacher.edit', $this->user('head')))->assertNotFound();
    }

    private function newClass(int $isFreeze = 0): int
    {
        return DB::table('class_transactions')->insertGetId([
            'class_type_id' => DB::table('class_types')->value('id'), 'Status' => 'aktif', 'is_freeze' => $isFreeze,
            'class_transaction_price' => 100000, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_replace_does_not_duplicate_a_class_the_replacement_already_teaches(): void
    {
        $a = User::factory()->create(['role' => 'teacher']);
        $b = User::factory()->create(['role' => 'teacher']);
        $shared = $this->newClass();
        $onlyA = $this->newClass();
        $frozenA = $this->newClass(1);
        foreach ([[$shared, $a], [$shared, $b], [$onlyA, $a], [$frozenA, $a]] as [$class, $t]) {
            DB::table('mapping_class_teachers')->insert(['class_id' => $class, 'user_id' => $t->id]);
        }

        $this->asRole('head')->post(route('head.teacher.replace', [$a, $b->id]))->assertRedirect();

        $this->assertNull(User::find($a->id));
        $this->assertSame(1, DB::table('mapping_class_teachers')->where('class_id', $shared)->count());
        $this->assertSame(1, DB::table('mapping_class_teachers')->where(['class_id' => $shared, 'user_id' => $b->id])->count());
        $this->assertSame(1, DB::table('mapping_class_teachers')->where(['class_id' => $onlyA, 'user_id' => $b->id])->count());
        $this->assertSame(1, DB::table('mapping_class_teachers')->where(['class_id' => $frozenA, 'user_id' => $b->id])->count());
        $this->assertSame(0, DB::table('mapping_class_teachers')->where('user_id', $a->id)->count());
    }

    public function test_mappings_of_deleted_classes_do_not_count_as_classes(): void
    {
        $teacher = User::factory()->create(['role' => 'teacher']);
        DB::table('mapping_class_teachers')->insert(['class_id' => 987654321, 'user_id' => $teacher->id]);

        $this->asRole('head')->get(route('head.teacher.switch', $teacher))->assertOk()->assertSeeText('has no classes');
        $this->post(route('head.teacher.destroy', $teacher))->assertRedirect()
            ->assertSessionHas('msg', 'Success Delete Data Teacher');
        $this->assertNull(User::find($teacher->id));
    }
}
