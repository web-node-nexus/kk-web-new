<?php

namespace App\Support;

class AdminPermissions
{
    /**
     * Catalog of admin permissions grouped for the Roles UI.
     *
     * @return array<string, array{label: string, items: array<string, string>}>
     */
    public static function catalog(): array
    {
        return [
            'Dashboard' => [
                'label' => 'Dashboard',
                'items' => [
                    'dashboard.view' => 'Dashboard',
                    'overview.view' => 'Overview',
                    'module.client-projects' => 'Projects',
                    'module.todos' => 'To-do List',
                ],
            ],
            'Hiring' => [
                'label' => 'Applications & Hiring',
                'items' => [
                    'module.applications' => 'Job Applications',
                    'module.internships' => 'Internships',
                    'module.candidates' => 'Candidates',
                    'module.interviews' => 'Interviews',
                    'module.jobs' => 'Job Openings',
                    'module.departments' => 'Departments',
                    'career-page.view' => 'Career Page',
                    'module.team-members' => 'Team Members (website)',
                    'module.portfolio' => 'Portfolio Projects',
                ],
            ],
            'Employees' => [
                'label' => 'Employees & HR Ops',
                'items' => [
                    'module.employees' => 'Employees',
                    'module.attendance' => 'Attendance',
                    'module.leaves' => 'Leave Requests',
                    'module.payroll' => 'Payroll',
                    'module.tasks' => 'Tasks',
                    'roles.manage' => 'Roles & Permissions',
                ],
            ],
            'Communication' => [
                'label' => 'Communication',
                'items' => [
                    'module.contacts' => 'Contact Inquiries',
                    'module.projects' => 'Project Requests',
                    'module.announcements' => 'Announcements',
                    'module.chat' => 'Live Chat',
                ],
            ],
            'Reports' => [
                'label' => 'Reports & Analytics',
                'items' => [
                    'reports.view' => 'Reports',
                    'analytics.view' => 'Analytics',
                ],
            ],
            'Settings' => [
                'label' => 'Settings',
                'items' => [
                    'settings.view' => 'Settings',
                ],
            ],
            'EmployeePanel' => [
                'label' => 'Employee panel — sirf tick wali cheezein employee login me dikhengi',
                'items' => [
                    'employee.attendance' => 'Attendance — check in / check out',
                    'employee.leaves' => 'Leaves',
                    'employee.payroll' => 'Payroll',
                    'employee.interviews' => 'Assigned interviews',
                    'employee.announcements' => 'Announcements',
                    'employee.tasks' => 'My Tasks',
                    'employee.chat' => 'Live Chat',
                ],
            ],
        ];
    }

    /** @return list<string> */
    public static function allKeys(): array
    {
        $keys = [];
        foreach (self::catalog() as $group) {
            foreach (array_keys($group['items']) as $key) {
                $keys[] = $key;
            }
        }

        return $keys;
    }

    public static function labelFor(string $key): string
    {
        foreach (self::catalog() as $group) {
            if (isset($group['items'][$key])) {
                return $group['items'][$key];
            }
        }

        return $key;
    }

    /** Map sidebar / page key → permission key */
    public static function forNavItem(array $link): ?string
    {
        if (($link['route'] ?? '') === 'admin.dashboard') {
            return 'dashboard.view';
        }
        if (($link['route'] ?? '') === 'admin.overview') {
            return 'overview.view';
        }
        if (($link['route'] ?? '') === 'admin.works.index') {
            return 'module.client-projects';
        }
        if (($link['route'] ?? '') === 'admin.career-page') {
            return 'career-page.view';
        }
        if (($link['route'] ?? '') === 'admin.reports') {
            return 'reports.view';
        }
        if (($link['route'] ?? '') === 'admin.analytics') {
            return 'analytics.view';
        }
        if (($link['route'] ?? '') === 'admin.settings') {
            return 'settings.view';
        }
        if (isset($link['module'])) {
            return $link['module'] === 'roles'
                ? 'roles.manage'
                : 'module.'.$link['module'];
        }

        return null;
    }

    public static function forModule(string $module): string
    {
        return $module === 'roles' ? 'roles.manage' : 'module.'.$module;
    }

    public static function forNamedRoute(string $routeName): ?string
    {
        return match ($routeName) {
            'admin.dashboard' => 'dashboard.view',
            'admin.notifications.index', 'admin.messages.index', 'admin.inbox.summary' => 'dashboard.view',
            'admin.overview' => 'overview.view',
            'admin.works.index', 'admin.works.create', 'admin.works.store',
            'admin.works.show', 'admin.works.edit', 'admin.works.update', 'admin.works.destroy',
            'admin.works.payment', 'admin.works.remind',
            'admin.works.installments.store', 'admin.works.installments.pay',
            'admin.works.installments.remind', 'admin.works.installments.destroy',
            'admin.works.members.store', 'admin.works.members.destroy',
            'admin.works.renewals.store', 'admin.works.renewals.update', 'admin.works.renewals.destroy',
            'admin.works.receipts.store', 'admin.works.receipts.destroy' => 'module.client-projects',
            'admin.todos.index', 'admin.todos.store', 'admin.todos.update', 'admin.todos.toggle', 'admin.todos.destroy' => null,
            'admin.career-page' => 'career-page.view',
            'admin.reports' => 'reports.view',
            'admin.analytics' => 'analytics.view',
            'admin.settings', 'admin.settings.update' => 'settings.view',
            'admin.roles.index', 'admin.roles.create', 'admin.roles.store',
            'admin.roles.edit', 'admin.roles.update', 'admin.roles.destroy',
            'admin.roles.assign', 'admin.roles.create-user' => 'roles.manage',
            'admin.applications.status', 'admin.applications.bulk-status' => 'module.applications',
            'admin.interviews.notify-hr', 'admin.interviews.bulk-notify-hr' => 'module.interviews',
            'admin.employees.search-hr' => 'module.interviews',
            'admin.employees.generate-code' => 'module.employees',
            'admin.attendance.index' => 'module.attendance',
            'admin.leaves.index', 'admin.leaves.decide' => 'module.leaves',
            'admin.payroll.index', 'admin.payroll.create', 'admin.payroll.store',
            'admin.payroll.edit', 'admin.payroll.update', 'admin.payroll.mark-paid',
            'admin.payroll.receipt', 'admin.payroll.destroy' => 'module.payroll',
            'admin.contacts.index', 'admin.contacts.show', 'admin.contacts.status',
            'admin.contacts.destroy' => 'module.contacts',
            'admin.announcements.index', 'admin.announcements.create', 'admin.announcements.store',
            'admin.announcements.edit', 'admin.announcements.update', 'admin.announcements.publish',
            'admin.announcements.destroy' => 'module.announcements',
            'admin.tasks.index', 'admin.tasks.create', 'admin.tasks.store',
            'admin.tasks.show', 'admin.tasks.reply', 'admin.tasks.status',
            'admin.tasks.destroy' => 'module.tasks',
            'admin.chat.index', 'admin.chat.messages', 'admin.chat.send' => 'module.chat',
            default => null,
        };
    }
}
