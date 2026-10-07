<?php

namespace Tests\Feature\Staff;

class StaffLinksTest extends StaffTestCase
{
    /** Parameterless staff pages; each domain task appends its own. Paths are relative to the prefix. */
    private const PAGES = ['', 'stock', 'teacher', 'teacher/add', 'report/class', 'report/active/student', 'report/stock', 'report/teacher', 'finance', 'finance/add', 'transaction', 'transaction/add', 'student', 'student/add', 'class', 'class/add', 'class/add/course', 'class/freeze', 'class/type', 'class/schedule/multiple'];

    public function test_staff_pages_link_only_to_the_viewers_own_prefix(): void
    {
        foreach (['admin' => 'head', 'head' => 'admin'] as $role => $other) {
            foreach (self::PAGES as $page) {
                $html = $this->asRole($role)->get("/{$role}/{$page}")->assertOk()->getContent();
                $this->assertStringNotContainsString(url("/{$other}/"), $html, "{$role} /{$page} links to /{$other}");
            }
        }
    }

    public function test_as_role_can_switch_roles_within_one_test(): void
    {
        $this->asRole('admin')->get('/admin')->assertOk();
        $this->asRole('head')->get('/head')->assertOk();
    }
}
