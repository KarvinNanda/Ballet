<?php

namespace Tests\Feature\Staff;

use App\Models\ClassTransaction;
use Illuminate\Support\Facades\DB;

class ClassPagesTest extends StaffTestCase
{
    /** An active, non-frozen class that has a schedule (the detail page needs one). */
    protected function runningClass(): ClassTransaction
    {
        return ClassTransaction::where('Status', 'aktif')->where('is_freeze', 0)
            ->whereIn('id', DB::table('schedules')->pluck('class_id'))->orderBy('id')->firstOrFail();
    }

    protected function frozenClass(): ClassTransaction
    {
        $class = ClassTransaction::where('is_freeze', 1)->whereIn('id', DB::table('schedules')->pluck('class_id'))->first();
        if ($class === null) {
            $class = ClassTransaction::whereIn('id', DB::table('schedules')->pluck('class_id'))->orderByDesc('id')->firstOrFail();
            DB::table('class_transactions')->where('id', $class->id)->update(['is_freeze' => 1]);
        }

        return $class->fresh();
    }

    public function test_list_shows_twenty_rows_per_page(): void
    {
        $this->assertSame(20, $this->asRole('admin')->get(route('admin.class.index'))->assertOk()->viewData('classes')->perPage());
        $this->assertSame(20, $this->asRole('head')->get(route('head.class.sort', ['column' => 'class_name', 'direction' => 'asc']))->viewData('classes')->perPage());
        $this->assertSame(20, $this->asRole('head')->get(route('head.class.freeze.index'))->viewData('classes')->perPage());
    }

    public function test_sort_keeps_keyword_and_status(): void
    {
        $classes = $this->asRole('admin')->get(route('admin.class.sort', ['column' => 'class_name', 'direction' => 'asc', 'status' => 'non-aktif']))
            ->assertOk()->viewData('classes');

        $this->assertNotEmpty($classes->items());
        $this->assertSame(['non-aktif'], collect($classes->items())->pluck('Status')->unique()->values()->all());
    }

    public function test_sort_links_carry_the_filters(): void
    {
        $this->asRole('head')->get(route('head.class.index', ['keyword' => 'a', 'status' => 'aktif']))
            ->assertSee('/head/class/sorting/class_name/asc?keyword=a&status=aktif');
    }

    public function test_active_row_menu_offers_freeze_and_confirmed_actions(): void
    {
        // Newest active class: the list is ordered by id desc, so it is on page 1.
        $class = ClassTransaction::where('Status', 'aktif')->where('is_freeze', 0)->orderByDesc('id')->firstOrFail();
        $html = $this->asRole('head')->get(route('head.class.index'))->getContent();

        $this->assertStringContainsString('action="'.route('head.class.level').'"', $html);
        $this->assertStringContainsString('<input type="hidden" name="classId" value="'.$class->id.'">', $html);
        $this->assertStringContainsString('action="'.route('head.class.status', $class).'" data-confirm="Set ', $html);
        $this->assertStringContainsString('action="'.route('head.class.destroy', $class).'" data-confirm="Delete ', $html);
    }

    public function test_inactive_class_has_no_detail_schedule_or_freeze(): void
    {
        $inactive = ClassTransaction::where('Status', 'non-aktif')->where('is_freeze', 0)->firstOrFail();
        $html = $this->asRole('admin')->get(route('admin.class.index', ['status' => 'non-aktif']))->getContent();

        $this->assertStringNotContainsString(route('admin.class.show', $inactive), $html);
        $this->assertStringNotContainsString(route('admin.class.level'), $html);
    }

    public function test_empty_result_shows_empty_state(): void
    {
        $this->asRole('admin')->get(route('admin.class.index', ['keyword' => 'zzz-no-such-class']))
            ->assertSeeText('No classes found')->assertDontSee('<table', false);
    }

    public function test_freeze_page_names_the_class_and_warns_it_cannot_be_undone(): void
    {
        $class = $this->runningClass();
        $course = $class->Type?->class_name;

        $html = $this->asRole('head')->post(route('head.class.level'), ['classId' => $class->id])->assertOk()
            ->assertSee('<h1 class="page-title">Freeze class '.e($course), false)
            ->assertSeeText('This cannot be undone from the app.')
            ->assertSee('action="'.route('head.class.level.store').'" data-confirm="Freeze '.e($course), false)
            ->assertSeeText('Freeze class')
            ->assertDontSee('Level Up')
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/<script\b(?![^>]*\ssrc=)[^>]*>/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<[^>]+\son[a-z]+\s*=/i', $html);
    }

    public function test_detail_opens_students_by_default(): void
    {
        $class = $this->runningClass();

        $this->asRole('head')->get(route('head.class.show', $class))->assertOk()
            ->assertSee('class="tab-pane fade show active" id="pane-students"', false)
            ->assertSee('class="tab-pane fade" id="pane-teachers"', false)
            ->assertSee('<h1 class="page-title summary-name">'.e($class->Type?->class_name), false);
    }

    public function test_teachers_tab_opens_with_the_query(): void
    {
        $class = $this->runningClass();
        $this->asRole('admin')->get(route('admin.class.show', ['class' => $class, 'tab' => 'teachers']))
            ->assertSee('class="tab-pane fade show active" id="pane-teachers"', false);
    }

    public function test_teachers_pager_stays_on_teachers_tab(): void
    {
        $class = $this->runningClass();
        $this->asRole('admin')->get(route('admin.class.show', ['class' => $class, 'teachers' => 2]))
            ->assertSee('class="tab-pane fade show active" id="pane-teachers"', false);
    }

    public function test_destructive_class_actions_ask_first(): void
    {
        $class = $this->runningClass();
        $html = $this->asRole('head')->get(route('head.class.show', $class))->getContent();

        $this->assertStringContainsString('action="'.route('head.class.reset-quota', $class).'" data-confirm="Reset quota', $html);
        $student = DB::table('mapping_class_children')->join('students', 'students.id', 'mapping_class_children.student_id')
            ->where('mapping_class_children.class_id', $class->id)->where('students.Status', '!=', 'non-aktif')->value('students.id');
        if ($student !== null) {
            $this->assertStringContainsString('action="'.route('head.class.student.destroy', ['student' => $student, 'class' => $class->id]).'" data-confirm="Remove ', $html);
            $this->assertStringContainsString('action="'.route('head.class.student.generate-transaction', ['student' => $student, 'class' => $class->id]).'" data-confirm="Generate ', $html);
        }
        $teacher = DB::table('mapping_class_teachers')->where('class_id', $class->id)->value('user_id');
        if ($teacher !== null) {
            $this->assertStringContainsString('action="'.route('head.class.teacher.destroy', ['teacher' => $teacher, 'class' => $class->id]).'" data-confirm="Remove ', $html);
        }
    }

    public function test_frozen_detail_is_read_only(): void
    {
        $class = $this->frozenClass();

        $this->asRole('head')->get(route('head.class.freeze.show', $class))->assertOk()
            ->assertSee('id="pane-students"', false)
            ->assertDontSee('/class/student/delete/', false)
            ->assertDontSee('/class/teacher/delete/', false)
            ->assertDontSee('/class/reset/quota/', false)
            ->assertDontSee('/class/student/add/', false);
    }

    public function test_add_teacher_and_add_student_pages_name_the_class_and_link_back(): void
    {
        $class = $this->runningClass();
        $course = e($class->Type?->class_name);

        $this->asRole('head')->get(route('head.class.teacher.create', $class))->assertOk()
            ->assertSee('<h1 class="page-title">Add teacher to '.$course, false)
            ->assertSee('href="'.route('head.class.show', ['class' => $class->id, 'tab' => 'teachers']).'"', false);

        $this->asRole('admin')->get(route('admin.class.student.create', $class))->assertOk()
            ->assertSee('<h1 class="page-title">Add student to '.$course, false)
            ->assertSee('action="'.route('admin.class.student.create', $class).'"', false)
            ->assertSee('href="'.route('admin.class.show', $class->id).'"', false);
    }

    public function test_add_class_form_uses_the_field_component(): void
    {
        $this->asRole('admin')->get(route('admin.class.create'))->assertOk()
            ->assertSee('<select id="field-inputType" name="inputType"', false)
            ->assertSee('<select id="field-inputTeacher" name="inputTeacher"', false);
    }

    public function test_update_price_page_names_the_class(): void
    {
        $class = $this->frozenClass();

        $html = $this->asRole('head')->get(route('head.class.freeze.edit', $class))->assertOk()
            ->assertSee('<h1 class="page-title">Update price · '.e($class->Type?->class_name), false)
            ->assertSee('name="inputPrice"', false)
            ->assertSee('name="return_url"', false)
            ->getContent();

        $this->assertDoesNotMatchRegularExpression('/<script\b(?![^>]*\ssrc=)[^>]*>/i', $html);
        $this->assertDoesNotMatchRegularExpression('/<[^>]+\son[a-z]+\s*=/i', $html);
    }

    public function test_frozen_list_has_the_new_header(): void
    {
        $this->asRole('admin')->get(route('admin.class.freeze.index'))->assertOk()
            ->assertSee('<h1 class="page-title">Frozen classes</h1>', false);
    }

    public function test_unknown_or_students_tab_wins_over_the_teachers_page_param(): void
    {
        $class = $this->runningClass();
        $this->asRole('admin')->get(route('admin.class.show', ['class' => $class, 'teachers' => 2, 'students' => 2, 'tab' => 'students']))
            ->assertSee('class="tab-pane fade show active" id="pane-students"', false)
            ->assertSee('class="tab-pane fade" id="pane-teachers"', false);
        $this->asRole('admin')->get(route('admin.class.show', ['class' => $class, 'tab' => 'bogus']))
            ->assertSee('class="tab-pane fade show active" id="pane-students"', false);
    }

    public function test_students_pager_links_keep_the_students_tab(): void
    {
        $class = $this->runningClass();
        foreach (\App\Models\Student::factory()->count(6)->create(['Status' => 'aktif']) as $student) {
            DB::table('mapping_class_children')->insert(['class_id' => $class->id, 'student_id' => $student->id, 'quota' => 0]);
        }

        $html = $this->asRole('admin')->get(route('admin.class.show', ['class' => $class, 'tab' => 'teachers']))->getContent();

        preg_match_all('/href="([^"]*[?&;]students=2[^"]*)"/', $html, $m);
        $this->assertNotEmpty($m[1], 'students pager link not rendered');
        foreach ($m[1] as $href) {
            $this->assertStringContainsString('tab=students', $href);
            $this->assertStringNotContainsString('tab=teachers', $href);
        }
    }
}
