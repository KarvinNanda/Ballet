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
            ['label' => 'Class', 'route' => 'admin.class.index', 'icon' => 'bi-easel', 'bottom' => true,
                'active' => ['admin/class*', 'admin/view/class*', 'admin/detail/class/*',
                    'admin/view/add/*', 'admin/view/addMultipleSchedule/*', 'admin/schedule/*']],
            ['label' => 'Class Freeze', 'route' => 'admin.class.freeze.index', 'icon' => 'bi-snow',
                'active' => ['admin/class/freeze*', 'admin/view/class/freeze*', 'admin/detail/class/freeze/*']],
            ['label' => 'Course', 'route' => 'admin.class-type.index', 'icon' => 'bi-journal-bookmark',
                'active' => ['admin/class/type*', 'admin/class/add/course*']],
            ['label' => 'Student', 'route' => 'admin.student.index', 'icon' => 'bi-people',
                'active' => ['admin/student*']],
            ['label' => 'Teacher', 'route' => 'admin.teacher.index', 'icon' => 'bi-person-badge',
                'active' => ['admin/teacher', 'admin/teacher/*']],
            ['label' => 'Finance', 'route' => 'admin.finance.index', 'icon' => 'bi-wallet2',
                'active' => ['admin/finance*']],
        ]],
        ['title' => 'Operasional', 'items' => [
            ['label' => 'Stock', 'route' => 'admin.stock.index', 'icon' => 'bi-box-seam',
                'active' => ['admin/stock*']],
            ['label' => 'Transaction', 'route' => 'admin.transaction.index', 'icon' => 'bi-receipt', 'bottom' => true,
                'active' => ['admin/transaction*']],
        ]],
        ['title' => 'Report', 'items' => [
            ['label' => 'Teacher', 'route' => 'admin.report.teacher', 'icon' => 'bi-person-lines-fill',
                'active' => ['admin/report/teacher*']],
            ['label' => 'Class Attendance', 'route' => 'admin.report.class', 'icon' => 'bi-clipboard-check',
                'active' => ['admin/report/class*']],
            ['label' => 'Stock', 'route' => 'admin.report.stock', 'icon' => 'bi-bar-chart',
                'active' => ['admin/report/stock*']],
            ['label' => 'Active Student', 'route' => 'admin.report.active-student', 'icon' => 'bi-person-check',
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
            ['label' => 'Class', 'route' => 'head.class.index', 'icon' => 'bi-easel', 'bottom' => true,
                'active' => ['head/class*', 'head/detail/class/*', 'head/view/class/sorting/*',
                    'head/view/add/*', 'head/schedule/*']],
            ['label' => 'Class Freeze', 'route' => 'head.class.freeze.index', 'icon' => 'bi-snow',
                'active' => ['head/class/freeze*', 'head/view/class/freeze*', 'head/detail/class/freeze/*', 'head/update/class/freeze/*']],
            ['label' => 'Course', 'route' => 'head.class-type.index', 'icon' => 'bi-journal-bookmark',
                'active' => ['head/class/type*', 'head/class/add/course*']],
            ['label' => 'Student', 'route' => 'head.student.index', 'icon' => 'bi-people',
                'active' => ['head/student*']],
            ['label' => 'Teacher', 'route' => 'head.teacher.index', 'icon' => 'bi-person-badge',
                'active' => ['head/teacher*']],
            ['label' => 'Finance', 'route' => 'head.finance.index', 'icon' => 'bi-wallet2',
                'active' => ['head/finance*']],
        ]],
        ['title' => 'Operasional', 'items' => [
            ['label' => 'Stock', 'route' => 'head.stock.index', 'icon' => 'bi-box-seam',
                'active' => ['head/stock*']],
            ['label' => 'Transaction', 'route' => 'head.transaction.index', 'icon' => 'bi-receipt', 'bottom' => true,
                'active' => ['head/transaction*']],
            ['label' => 'Rule & Regulation', 'route' => 'Rules', 'icon' => 'bi-file-text',
                'active' => ['head/report/rule*']],
        ]],
        ['title' => 'Report', 'items' => [
            ['label' => 'Teacher', 'route' => 'head.report.teacher', 'icon' => 'bi-person-lines-fill',
                'active' => ['head/report/teacher*']],
            ['label' => 'Class Attendance', 'route' => 'head.report.class', 'icon' => 'bi-clipboard-check',
                'active' => ['head/report/class*']],
            ['label' => 'Stock', 'route' => 'head.report.stock', 'icon' => 'bi-bar-chart',
                'active' => ['head/report/stock*']],
            ['label' => 'Active Student', 'route' => 'head.report.active-student', 'icon' => 'bi-person-check',
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
