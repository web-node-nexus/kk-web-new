<?php

namespace App\Support;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SiteSettings
{
    public const CACHE_KEY = 'kk_site_settings';

    /** @return array<string, string|null> */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, function () {
            return SiteSetting::query()->pluck('value', 'key')->toArray();
        });
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = self::all();

        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function bool(string $key, bool $default = true): bool
    {
        $val = self::get($key, $default ? '1' : '0');

        return in_array((string) $val, ['1', 'true', 'on', 'yes'], true);
    }

    public static function set(string $key, mixed $value): void
    {
        SiteSetting::put($key, is_bool($value) ? ($value ? '1' : '0') : $value);
    }

    /** @param array<string, mixed> $pairs */
    public static function setMany(array $pairs): void
    {
        foreach ($pairs as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
        Cache::forget(SiteSetting::CACHE_KEY);
    }

    /**
     * Schema for admin settings UI.
     *
     * @return array<string, array{label: string, fields: list<array<string, mixed>>}>
     */
    public static function schema(): array
    {
        return [
            'company' => [
                'label' => 'Company profile',
                'fields' => [
                    ['key' => 'company_name', 'label' => 'Company name', 'type' => 'text'],
                    ['key' => 'support_email', 'label' => 'Support email', 'type' => 'email'],
                    ['key' => 'support_phone', 'label' => 'Phone', 'type' => 'text'],
                    ['key' => 'company_address', 'label' => 'Address', 'type' => 'text'],
                    ['key' => 'timezone', 'label' => 'Timezone', 'type' => 'text'],
                    ['key' => 'currency', 'label' => 'Currency', 'type' => 'text'],
                    ['key' => 'default_hr_email', 'label' => 'Default HR email', 'type' => 'email'],
                    ['key' => 'late_checkin_after', 'label' => 'Late check-in after (HH:MM)', 'type' => 'text'],
                ],
            ],
            'website' => [
                'label' => 'Website features (ON / OFF)',
                'fields' => [
                    ['key' => 'feature_careers', 'label' => 'Careers / Job applications', 'type' => 'toggle', 'hint' => 'Off hone par apply form band'],
                    ['key' => 'feature_contact_form', 'label' => 'Contact form', 'type' => 'toggle'],
                    ['key' => 'feature_project_form', 'label' => 'Start project form', 'type' => 'toggle'],
                    ['key' => 'feature_newsletter', 'label' => 'Newsletter signup', 'type' => 'toggle'],
                    ['key' => 'feature_announcements', 'label' => 'Announcements module', 'type' => 'toggle'],
                ],
            ],
            'employee' => [
                'label' => 'Employee panel features (ON / OFF)',
                'fields' => [
                    ['key' => 'feature_employee_attendance', 'label' => 'Attendance (check-in / out)', 'type' => 'toggle'],
                    ['key' => 'feature_employee_leaves', 'label' => 'Leave requests', 'type' => 'toggle'],
                    ['key' => 'feature_employee_payroll', 'label' => 'Payroll / payslips', 'type' => 'toggle'],
                    ['key' => 'feature_employee_interviews', 'label' => 'Assigned interviews (HR)', 'type' => 'toggle'],
                ],
            ],
            'mail' => [
                'label' => 'Email notifications (ON / OFF)',
                'fields' => [
                    ['key' => 'mail_application_status', 'label' => 'Application status emails', 'type' => 'toggle'],
                    ['key' => 'mail_leave_status', 'label' => 'Leave approve / reject emails', 'type' => 'toggle'],
                    ['key' => 'mail_hr_interview', 'label' => 'HR interview assignment emails', 'type' => 'toggle'],
                    ['key' => 'mail_employee_welcome', 'label' => 'Employee welcome login emails', 'type' => 'toggle'],
                    ['key' => 'mail_task_assigned', 'label' => 'Task assignment emails', 'type' => 'toggle'],
                    ['key' => 'mail_project_updates', 'label' => 'Client project / payment / EMI emails', 'type' => 'toggle'],
                ],
            ],
        ];
    }
}
