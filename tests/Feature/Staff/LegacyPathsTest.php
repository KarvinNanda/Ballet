<?php

namespace Tests\Feature\Staff;

/** get-price is omitted on purpose: it is an AJAX endpoint that answers 422 without its query parameters. */
class LegacyPathsTest extends StaffTestCase
{
    private const ADMIN = [
            '/admin',
            '/admin/class/active',
            '/admin/class/add',
            '/admin/class/add/course',
            '/admin/class/non/active',
            '/admin/class/type',
            '/admin/report/active/student',
            '/admin/report/class',
            '/admin/stock',
            '/admin/student/active',
            '/admin/student/form',
            '/admin/student/non/active',
            '/admin/student/view',
            '/admin/teacher/form',
            '/admin/teacher/search',
            '/admin/teacher/view',
            '/admin/transaction',
            '/admin/transaction/add',
            '/admin/view/addMultipleSchedule/class',
            '/admin/view/class',
            '/admin/view/class/freeze',
    ];

    private const HEAD = [
            '/head',
            '/head/admin',
            '/head/admin/add',
            '/head/class',
            '/head/class/active',
            '/head/class/add',
            '/head/class/add/course',
            '/head/class/non/active',
            '/head/class/type',
            '/head/finance',
            '/head/finance/add',
            '/head/report/active/student',
            '/head/report/class',
            '/head/report/rule',
            '/head/report/rule/add',
            '/head/report/stock',
            '/head/report/teacher',
            '/head/stock',
            '/head/stock/add',
            '/head/student',
            '/head/student/active',
            '/head/student/add',
            '/head/student/non/active',
            '/head/teacher',
            '/head/teacher/add',
            '/head/transaction',
            '/head/transaction/add',
            '/head/transaction/search',
            '/head/view/class/freeze',
    ];

    public function test_old_admin_paths_still_resolve(): void
    {
        $this->assertAllResolve('admin', self::ADMIN);
    }

    public function test_old_head_paths_still_resolve(): void
    {
        $this->assertAllResolve('head', self::HEAD);
    }

    private function assertAllResolve(string $role, array $paths): void
    {
        $bad = [];
        foreach ($paths as $path) {
            $this->asRole($role);
            $status = $this->followingRedirects()->get($path)->getStatusCode();
            if ($status !== 200) {
                $bad[] = "$path => $status";
            }
        }

        $this->assertSame([], $bad, "Old $role paths that do not end in 200");
    }
}
