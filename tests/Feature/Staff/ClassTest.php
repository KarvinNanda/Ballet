<?php

namespace Tests\Feature\Staff;

use App\Models\ClassTransaction;
use Illuminate\Support\Facades\DB;

class ClassTest extends StaffTestCase
{
    public function test_admin_detail_page_deletes_nothing(): void // purged trial students
    {
        $class = $this->runningClass();
        // A trial student with Quota >= 2 in this class: the old page deleted their mappings on GET.
        $studentId = DB::table('mapping_class_children')->where('class_id', $class->id)->whereNotNull('student_id')->value('student_id');
        DB::table('students')->where('id', $studentId)->update(['Status' => 'trial', 'Quota' => 2]);
        $before = DB::table('mapping_class_children')->count();

        $this->asRole('admin')->get(route('admin.class.show', $class))->assertOk();

        $this->assertSame($before, DB::table('mapping_class_children')->count());
    }

    public function test_freeze_detail_page_deletes_nothing(): void
    {
        $class = $this->frozenClass();
        DB::table('mapping_class_children')->insert(['class_id' => $class->id, 'student_id' => $this->trialStudentId()]);
        $before = DB::table('mapping_class_children')->count();

        $this->asRole('head')->get(route('head.class.freeze.show', $class))->assertOk();

        $this->assertSame($before, DB::table('mapping_class_children')->count());
    }

    public function test_head_level_up_page_renders(): void // missing $return_url → 500
    {
        $class = $this->runningClass();
        $this->asRole('head')->post(route('head.class.level'), ['classId' => $class->id])
            ->assertOk()
            ->assertSee('name="classId" value="'.$class->id.'"', false)
            ->assertSee('name="return_url"', false);
    }

    public function test_level_up_confirm_freezes_the_class_and_returns(): void
    {
        $class = $this->runningClass();
        $back = route('admin.class.index');

        $this->asRole('admin')->post(route('admin.class.level.store'), ['classId' => $class->id, 'return_url' => $back])
            ->assertRedirect($back);

        $this->assertSame(1, (int) DB::table('class_transactions')->where('id', $class->id)->value('is_freeze'));
    }

    public function test_list_freeze_button_opens_the_level_up_page(): void
    {
        $this->asRole('admin')->get(route('admin.class.index'))
            ->assertOk()
            ->assertSee('action="'.route('admin.class.level').'"', false)
            ->assertDontSee('action="'.route('admin.class.level.store').'"', false);
    }

    public function test_only_head_updates_the_freeze_price(): void
    {
        $class = $this->frozenClass();
        $price = $class->class_transaction_price;

        $this->asRole('admin')->get(route('admin.class.freeze.edit', $class))->assertForbidden();
        $this->post(route('admin.class.freeze.update', $class), ['inputPrice' => 1])->assertForbidden();
        $this->assertEquals($price, DB::table('class_transactions')->where('id', $class->id)->value('class_transaction_price'));

        $this->asRole('head')->get(route('head.class.freeze.edit', $class))->assertOk();
        $this->post(route('head.class.freeze.update', $class), ['inputPrice' => 123456, 'return_url' => route('head.class.freeze.index')])
            ->assertRedirect(route('head.class.freeze.index'));
        $this->assertEquals(123456, DB::table('class_transactions')->where('id', $class->id)->value('class_transaction_price'));
    }

    public function test_freeze_list_update_button_only_for_head(): void
    {
        $class = $this->frozenClass();

        $this->asRole('admin')->get(route('admin.class.freeze.index'))->assertOk()
            ->assertSee(route('admin.class.freeze.show', $class), false)
            ->assertDontSee(route('admin.class.freeze.edit', $class), false);

        $this->asRole('head')->get(route('head.class.freeze.index'))->assertOk()
            ->assertSee(route('head.class.freeze.edit', $class), false);
    }

    public function test_freeze_list_search_stays_on_the_freeze_page(): void
    {
        $this->asRole('head')->get(route('head.class.freeze.index'))
            ->assertOk()
            ->assertSee('action="'.route('head.class.freeze.index').'"', false);
    }

    public function test_freeze_list_status_filter_does_not_crash(): void // status branch used users/students columns without joining them
    {
        $this->asRole('head')->get(route('head.class.freeze.index', ['status' => 'aktif', 'keyword' => 'a']))->assertOk();
    }

    public function test_list_status_filter_shows_only_that_status(): void
    {
        $classes = $this->asRole('admin')->get(route('admin.class.index', ['status' => 'non-aktif']))
            ->assertOk()->viewData('classes');

        $this->assertNotEmpty($classes->items());
        $this->assertSame(['non-aktif'], collect($classes->items())->pluck('Status')->unique()->values()->all());
    }

    public function test_old_status_links_redirect_to_the_filtered_list(): void
    {
        $this->asRole('head')->get('/head/class/non/active')->assertRedirect(route('head.class.index', ['status' => 'non-aktif']));
        $this->get('/head/class/active')->assertRedirect(route('head.class.index', ['status' => 'aktif']));
    }

    public function test_insert_does_not_create_an_empty_student_mapping(): void
    {
        $this->asRole('head')->post(route('head.class.store'), $this->classPayload())->assertRedirect(route('head.class.index'));
        $this->assertSame(0, DB::table('mapping_class_children')->whereNull('student_id')->count());
        $this->assertSame(1, DB::table('mapping_class_teachers')->where('class_id', DB::table('class_transactions')->max('id'))->count());
    }

    public function test_insert_with_unknown_course_is_a_validation_error(): void
    {
        $before = DB::table('class_transactions')->count();

        $this->asRole('admin')->from(route('admin.class.create'))
            ->post(route('admin.class.store'), ['inputType' => 999999] + $this->classPayload())
            ->assertSessionHasErrors('inputType');

        $this->assertSame($before, DB::table('class_transactions')->count());
    }

    public function test_unknown_class_delete_is_404(): void
    {
        $this->asRole('admin')->post(route('admin.class.destroy', 999999))->assertNotFound();
    }

    public function test_sort_allows_status(): void
    {
        $this->asRole('admin')->get(route('admin.class.sort', ['column' => 'status', 'direction' => 'asc']))->assertOk();
    }

    public function test_class_type_price_is_validated(): void
    {
        $typeId = DB::table('class_types')->value('id');
        $price = DB::table('class_types')->where('id', $typeId)->value('class_price');

        $this->asRole('admin')->post(route('admin.class-type.update'), ['typeID' => $typeId, 'inputPrice' => 'abc'])
            ->assertSessionHasErrors('inputPrice');

        $this->assertEquals($price, DB::table('class_types')->where('id', $typeId)->value('class_price'));
    }

    public function test_class_type_list_is_paginated_for_both_roles(): void
    {
        foreach (['admin', 'head'] as $role) {
            $types = $this->asRole($role)->get(route("{$role}.class-type.index"))->assertOk()->viewData('types');
            $this->assertInstanceOf(\Illuminate\Contracts\Pagination\Paginator::class, $types);
        }
    }

    public function test_detail_pages_use_separate_pagers(): void
    {
        $class = $this->runningClass();

        $this->asRole('head')->get(route('head.class.show', $class))
            ->assertOk()
            ->assertViewHas('teachers', fn ($p) => $p->getPageName() === 'teachers')
            ->assertViewHas('students', fn ($p) => $p->getPageName() === 'students');
    }

    public function test_add_student_page_searches_with_own_route(): void
    {
        $class = $this->runningClass();

        $this->asRole('admin')->get(route('admin.class.student.create', $class))
            ->assertOk()
            ->assertSee('action="'.route('admin.class.student.create', $class).'"', false)
            ->assertDontSee(url('/head/'), false);
    }

    public function test_old_paths_redirect(): void
    {
        $class = $this->runningClass();
        $frozen = $this->frozenClass();

        $this->asRole('admin')->get('/admin/view/class')->assertRedirect('/admin/class');
        $this->get('/admin/view/class/freeze')->assertRedirect('/admin/class/freeze');
        $this->get("/admin/detail/class/{$class->id}")->assertRedirect(route('admin.class.show', $class->id));
        $this->get("/admin/detail/class/freeze/{$frozen->id}")->assertRedirect(route('admin.class.freeze.show', $frozen->id));

        $this->asRole('head')->get('/head/view/class/freeze')->assertRedirect('/head/class/freeze');
        $this->get("/head/detail/class/{$class->id}")->assertRedirect(route('head.class.show', $class->id));
    }

    public function test_class_sub_pages_render_with_own_prefix_links(): void
    {
        $class = $this->runningClass();
        $frozen = $this->frozenClass();
        $typeId = DB::table('class_types')->value('id');

        foreach (['admin' => 'head', 'head' => 'admin'] as $role => $other) {
            $pages = [
                $this->asRole($role)->get(route("{$role}.class.show", $class)),
                $this->get(route("{$role}.class.teacher.create", $class)),
                $this->get(route("{$role}.class.student.create", $class)),
                $this->get(route("{$role}.class.freeze.show", $frozen)),
                $this->post(route("{$role}.class.level"), ['classId' => $class->id]),
                $this->post(route("{$role}.class-type.edit"), ['typeID' => $typeId]),
            ];
            foreach ($pages as $response) {
                $this->assertStringNotContainsString(url("/{$other}/"), $response->assertOk()->getContent());
            }
        }
    }

    public function test_class_type_update_changes_the_price_and_returns(): void
    {
        $typeId = DB::table('class_types')->value('id');
        $back = route('head.class-type.index');

        $this->asRole('head')->post(route('head.class-type.update'), ['typeID' => $typeId, 'inputPrice' => 450000, 'return_url' => $back])
            ->assertRedirect($back);

        $this->assertEquals(450000, DB::table('class_types')->where('id', $typeId)->value('class_price'));
    }

    public function test_remove_student_and_teacher_from_a_class(): void
    {
        $class = $this->runningClass();
        $studentId = DB::table('mapping_class_children')->where('class_id', $class->id)->value('student_id');
        $teacherId = DB::table('mapping_class_teachers')->where('class_id', $class->id)->value('user_id');

        $this->asRole('admin')->post(route('admin.class.student.destroy', ['student' => $studentId, 'class' => $class->id]))
            ->assertRedirect(route('admin.class.show', $class));
        $this->post(route('admin.class.teacher.destroy', ['teacher' => $teacherId, 'class' => $class->id]))
            ->assertRedirect(route('admin.class.show', $class));

        $this->assertDatabaseMissing('mapping_class_children', ['class_id' => $class->id, 'student_id' => $studentId]);
        $this->assertDatabaseMissing('mapping_class_teachers', ['class_id' => $class->id, 'user_id' => $teacherId]);
    }

    public function test_add_active_student_creates_mapping_and_three_transactions(): void
    {
        $class = $this->runningClass();
        $studentId = DB::table('students')->where('Status', 'aktif')
            ->whereNotIn('id', DB::table('mapping_class_children')->where('class_id', $class->id)->pluck('student_id'))
            ->value('id');
        $before = DB::table('transactions')->where('students_id', $studentId)->count();

        $this->asRole('head')->post(route('head.class.student.store'), ['classId' => $class->id, 'studentId' => $studentId])
            ->assertRedirect(route('head.class.show', $class))
            ->assertSessionHas('msg');

        $this->assertDatabaseHas('mapping_class_children', ['class_id' => $class->id, 'student_id' => $studentId]);
        $this->assertSame($before + 3, DB::table('transactions')->where('students_id', $studentId)->count());
    }

    public function test_course_price_update_changes_only_unpaid_transactions(): void
    {
        foreach (['admin' => 300000, 'head' => 350000] as $role => $newPrice) {
            $class = $this->runningClass();
            $studentId = DB::table('students')->value('id');
            $paid = DB::table('transactions')->insertGetId(['students_id' => $studentId, 'class_transactions_id' => $class->id, 'payment_status' => 'Paid', 'price' => 111, 'transaction_date' => now()]);
            $unpaid = DB::table('transactions')->insertGetId(['students_id' => $studentId, 'class_transactions_id' => $class->id, 'payment_status' => 'Unpaid', 'price' => 111, 'transaction_date' => now()]);

            $this->asRole($role)->post(route("{$role}.class-type.update"), ['typeID' => $class->class_type_id, 'inputPrice' => $newPrice, 'return_url' => '/'])
                ->assertRedirect();

            $this->assertEquals(111, DB::table('transactions')->where('id', $paid)->value('price'), "{$role}: Paid row changed");
            $this->assertEquals($newPrice, DB::table('transactions')->where('id', $unpaid)->value('price'), "{$role}: Unpaid row not updated");
            $this->assertEquals($newPrice, DB::table('class_transactions')->where('id', $class->id)->value('class_transaction_price'));
        }
    }

    public function test_generate_transaction_for_a_student_not_in_the_class_is_404(): void
    {
        $class = $this->runningClass();
        $outsider = DB::table('students')
            ->whereNotIn('id', DB::table('mapping_class_children')->where('class_id', $class->id)->pluck('student_id'))
            ->value('id');
        $before = DB::table('transactions')->count();

        $this->asRole('admin')->post(route('admin.class.student.generate-transaction', ['student' => $outsider, 'class' => $class->id]))->assertNotFound();
        $this->post(route('admin.class.student.generate-transaction', ['student' => 999999, 'class' => $class->id]))->assertNotFound();

        $this->assertSame($before, DB::table('transactions')->count());
    }

    public function test_generate_transaction_for_a_class_member_inserts_three_rows(): void
    {
        $class = $this->runningClass();
        $member = DB::table('mapping_class_children')->where('class_id', $class->id)->value('student_id');
        $before = DB::table('transactions')->count();

        $this->asRole('head')->post(route('head.class.student.generate-transaction', ['student' => $member, 'class' => $class->id]))
            ->assertRedirect(route('head.class.show', $class));

        $this->assertSame($before + 3, DB::table('transactions')->count());
    }

    public function test_level_up_page_lists_all_students_without_a_pager(): void
    {
        $class = $this->runningClass();

        $this->asRole('admin')->post(route('admin.class.level'), ['classId' => $class->id])
            ->assertOk()
            ->assertViewHas('students', fn ($s) => $s instanceof \Illuminate\Support\Collection)
            ->assertDontSee('?page=', false);
    }

    private function runningClass(): ClassTransaction
    {
        return ClassTransaction::where('Status', 'aktif')->where('is_freeze', 0)->orderBy('id')->firstOrFail();
    }

    private function frozenClass(): ClassTransaction
    {
        return ClassTransaction::where('is_freeze', 1)->firstOrFail();
    }

    private function trialStudentId(): int
    {
        $id = DB::table('students')->value('id');
        DB::table('students')->where('id', $id)->update(['Status' => 'trial', 'Quota' => 3]);

        return $id;
    }

    /** Inputs of the class insert form: course (class type) and teacher. */
    private function classPayload(): array
    {
        return [
            'inputType' => DB::table('class_types')->orderBy('id')->value('id'),
            'inputTeacher' => DB::table('users')->where('role', 'teacher')->orderBy('id')->value('id'),
        ];
    }
}
