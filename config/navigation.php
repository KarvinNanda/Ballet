<?php

/*
| Sidebar and bottom navigation per role.
| group: title (null = always visible, otherwise a collapsible group), items
| item:  label, route (route name), icon (Bootstrap Icons class),
|        active (URL path patterns that highlight this item; the most specific match wins),
|        bottom (show in phone bottom nav, max 3 per role),
|        user_param (optional: route parameter filled with the logged-in user id)
*/

return [
    'admin' => [
        ['title' => null, 'items' => [
            ['label' => 'Dashboard', 'route' => 'admin', 'icon' => 'bi-grid-1x2', 'bottom' => true,
                'active' => ['admin']],
        ]],
        ['title' => 'Master', 'items' => [
            ['label' => 'Class', 'route' => 'adminClassView', 'icon' => 'bi-easel', 'bottom' => true,
                'active' => ['admin/view/class*', 'admin/class/*', 'admin/detail/class/*', 'admin/reset/class/*',
                    'admin/view/add/*', 'admin/view/addMultipleSchedule/*', 'admin/view/schedule/*', 'admin/view/update/*']],
            ['label' => 'Class Freeze', 'route' => 'adminClassFreezeView', 'icon' => 'bi-snow',
                'active' => ['admin/view/class/freeze*', 'admin/detail/class/freeze/*']],
            ['label' => 'Course', 'route' => 'adminClassTypePage', 'icon' => 'bi-journal-bookmark',
                'active' => ['admin/class/type*', 'admin/class/add/course*']],
            ['label' => 'Student', 'route' => 'adminStudentView', 'icon' => 'bi-people',
                'active' => ['admin/student/*']],
            ['label' => 'Teacher', 'route' => 'adminTeacherView', 'icon' => 'bi-person-badge',
                'active' => ['admin/teacher/*']],
        ]],
        ['title' => 'Operasional', 'items' => [
            ['label' => 'Stock', 'route' => 'adminStockPage', 'icon' => 'bi-box-seam',
                'active' => ['admin/stock*']],
            ['label' => 'Transaction', 'route' => 'adminTransactionPage', 'icon' => 'bi-receipt', 'bottom' => true,
                'active' => ['admin/transaction*']],
        ]],
        ['title' => 'Report', 'items' => [
            ['label' => 'Class Attendance', 'route' => 'adminClassReport', 'icon' => 'bi-clipboard-check',
                'active' => ['admin/report/class*']],
            ['label' => 'Active Student', 'route' => 'adminPrintActiveStudentPage', 'icon' => 'bi-person-check',
                'active' => ['admin/report/active/student*']],
        ]],
    ],

    'head' => [
        ['title' => null, 'items' => [
            ['label' => 'Dashboard', 'route' => 'head', 'icon' => 'bi-grid-1x2', 'bottom' => true,
                'active' => ['head']],
        ]],
        ['title' => 'Master', 'items' => [
            ['label' => 'Admin', 'route' => 'headAdminPage', 'icon' => 'bi-shield-lock',
                'active' => ['head/admin*']],
            ['label' => 'Class', 'route' => 'headClassPage', 'icon' => 'bi-easel', 'bottom' => true,
                'active' => ['head/class*', 'head/detail/class/*', 'head/reset/class/*', 'head/view/class/sorting/*',
                    'head/view/add/*', 'head/view/addMultipleSchedule/*', 'head/view/schedule/*']],
            ['label' => 'Class Freeze', 'route' => 'headClassFreezeView', 'icon' => 'bi-snow',
                'active' => ['head/view/class/freeze*', 'head/detail/class/freeze/*', 'head/update/class/freeze/*']],
            ['label' => 'Course', 'route' => 'headClassTypePage', 'icon' => 'bi-journal-bookmark',
                'active' => ['head/class/type*', 'head/class/add/course*']],
            ['label' => 'Student', 'route' => 'headStudentPage', 'icon' => 'bi-people',
                'active' => ['head/student*']],
            ['label' => 'Teacher', 'route' => 'headTeacherPage', 'icon' => 'bi-person-badge',
                'active' => ['head/teacher*']],
            ['label' => 'Finance', 'route' => 'headFinancePage', 'icon' => 'bi-wallet2',
                'active' => ['head/finance*']],
        ]],
        ['title' => 'Operasional', 'items' => [
            ['label' => 'Stock', 'route' => 'headStockPage', 'icon' => 'bi-box-seam',
                'active' => ['head/stock*']],
            ['label' => 'Transaction', 'route' => 'headTransactionPage', 'icon' => 'bi-receipt', 'bottom' => true,
                'active' => ['head/transaction*']],
            ['label' => 'Rule & Regulation', 'route' => 'Rules', 'icon' => 'bi-file-text',
                'active' => ['head/report/rule*']],
        ]],
        ['title' => 'Report', 'items' => [
            ['label' => 'Teacher', 'route' => 'headTeacherReportPage', 'icon' => 'bi-person-lines-fill',
                'active' => ['head/report/teacher*']],
            ['label' => 'Class Attendance', 'route' => 'headClassReport', 'icon' => 'bi-clipboard-check',
                'active' => ['head/report/class*']],
            ['label' => 'Stock', 'route' => 'headStockReport', 'icon' => 'bi-bar-chart',
                'active' => ['head/report/stock*']],
            ['label' => 'Active Student', 'route' => 'headPrintActiveStudentPage', 'icon' => 'bi-person-check',
                'active' => ['head/report/active/student*']],
        ]],
    ],

    'teacher' => [
        ['title' => null, 'items' => [
            ['label' => 'Dashboard', 'route' => 'teacher', 'icon' => 'bi-grid-1x2', 'bottom' => true,
                'active' => ['teacher']],
            ['label' => 'Class', 'route' => 'viewClass', 'icon' => 'bi-easel', 'bottom' => true,
                'active' => ['teacher/view/class', 'teacher/view/schedule/*', 'teacher/view/add/*',
                    'teacher/view/addMultipleSchedule/*', 'teacher/view/update/*']],
            ['label' => 'Schedule', 'route' => 'viewAllScheduleTeacher', 'icon' => 'bi-calendar3', 'bottom' => true, 'user_param' => 'id',
                'active' => ['teacher/view/class/schedule/*']],
        ]],
    ],

    'finance' => [
        ['title' => null, 'items' => [
            ['label' => 'Dashboard', 'route' => 'finance', 'icon' => 'bi-grid-1x2', 'bottom' => true,
                'active' => ['finance']],
            ['label' => 'Transaction', 'route' => 'financeTransaction', 'icon' => 'bi-receipt', 'bottom' => true,
                'active' => ['finance/transaction*']],
        ]],
        ['title' => 'Report', 'items' => [
            ['label' => 'Stock', 'route' => 'financeStockReport', 'icon' => 'bi-bar-chart', 'bottom' => true,
                'active' => ['finance/report/stock*', 'finance/stock/*', 'finance/in/*']],
            ['label' => 'Teacher', 'route' => 'financeTeacherReportPage', 'icon' => 'bi-person-lines-fill',
                'active' => ['finance/report/teacher*']],
            ['label' => 'Active Student', 'route' => 'financeStudentReportPage', 'icon' => 'bi-person-check',
                'active' => ['finance/report/finance/student*']],
        ]],
    ],
];
