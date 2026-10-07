<?php

namespace Tests\Feature\Staff;

use App\Models\User;

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
}
