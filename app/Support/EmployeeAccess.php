<?php

namespace App\Support;

class EmployeeAccess
{
    /** @var array<string, list<string>> */
    public const ALIASES = [
        'employee.attendance' => ['employee.attendance', 'module.attendance'],
        'employee.leaves' => ['employee.leaves', 'module.leaves'],
        'employee.payroll' => ['employee.payroll', 'module.payroll'],
        'employee.interviews' => ['employee.interviews', 'module.interviews'],
        'employee.announcements' => ['employee.announcements', 'module.announcements'],
        'employee.tasks' => ['employee.tasks', 'module.tasks'],
        'employee.chat' => ['employee.chat', 'module.chat'],
    ];

    /** @var array<string, string|null> */
    public const FEATURE_FLAGS = [
        'employee.attendance' => null,
        'employee.leaves' => 'feature_employee_leaves',
        'employee.payroll' => null,
        'employee.interviews' => 'feature_employee_interviews',
        'employee.announcements' => 'feature_announcements',
        'employee.tasks' => null,
        'employee.chat' => null,
    ];

    /** @return list<string> */
    public static function defaultKeys(): array
    {
        return array_keys(self::ALIASES);
    }

    public static function forRoute(string $routeName): ?string
    {
        return match (true) {
            str_starts_with($routeName, 'employee.attendance') => 'employee.attendance',
            str_starts_with($routeName, 'employee.leaves') => 'employee.leaves',
            str_starts_with($routeName, 'employee.payroll') => 'employee.payroll',
            str_starts_with($routeName, 'employee.interviews') => 'employee.interviews',
            str_starts_with($routeName, 'employee.announcements') => 'employee.announcements',
            str_starts_with($routeName, 'employee.tasks') => 'employee.tasks',
            str_starts_with($routeName, 'employee.chat') => 'employee.chat',
            default => null,
        };
    }
}
