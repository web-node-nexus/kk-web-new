<?php

namespace Database\Seeders;

use App\Models\AdminRole;
use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\Banner;
use App\Models\CmsPage;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\LeaveRequest;
use App\Models\PayrollRecord;
use App\Models\SupportTicket;
use App\Models\Testimonial;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AdminPanelSeeder extends Seeder
{
    public function run(): void
    {
        $deps = [
            ['name' => 'Engineering', 'code' => 'ENG', 'head_name' => 'Prasenjit Mishra'],
            ['name' => 'Product', 'code' => 'PRD', 'head_name' => 'Divyanshu Mishra'],
            ['name' => 'Design', 'code' => 'DSN', 'head_name' => 'Yashi Sachan'],
            ['name' => 'Marketing', 'code' => 'MKT', 'head_name' => 'Tapaswi Tiwari'],
            ['name' => 'HR & People', 'code' => 'HR', 'head_name' => 'KK Admin'],
        ];
        foreach ($deps as $d) {
            Department::query()->updateOrCreate(['code' => $d['code']], $d + ['is_active' => true, 'description' => $d['name'].' department']);
        }

        $eng = Department::query()->where('code', 'ENG')->first();
        $mkt = Department::query()->where('code', 'MKT')->first();
        $prd = Department::query()->where('code', 'PRD')->first();

        $jobs = [
            ['title' => 'Full Stack Developer', 'department_name' => 'Engineering', 'department_id' => $eng?->id, 'employment_type' => 'Full-time', 'location' => 'Kanpur / Hybrid', 'is_open' => true, 'sort_order' => 1],
            ['title' => 'UI/UX Designer', 'department_name' => 'Design', 'employment_type' => 'Full-time', 'location' => 'Remote', 'is_open' => true, 'sort_order' => 2],
            ['title' => 'Digital Marketing Executive', 'department_name' => 'Marketing', 'department_id' => $mkt?->id, 'employment_type' => 'Full-time', 'location' => 'Kanpur', 'is_open' => true, 'sort_order' => 3],
            ['title' => 'Product Manager', 'department_name' => 'Product', 'department_id' => $prd?->id, 'employment_type' => 'Full-time', 'location' => 'Hybrid', 'is_open' => true, 'sort_order' => 4],
            ['title' => 'Internship for Skill Upgrade', 'department_name' => 'Engineering', 'department_id' => $eng?->id, 'employment_type' => 'Internship · Remote / Hybrid', 'location' => 'Kanpur / Remote', 'is_open' => true, 'sort_order' => 5],
        ];
        foreach ($jobs as $job) {
            JobOpening::query()->updateOrCreate(
                ['title' => $job['title']],
                $job + ['description' => 'Join KK Digital and build impactful digital products.', 'openings_count' => 2]
            );
        }

        $sources = ['Career Website', 'LinkedIn', 'Indeed', 'Employee Referral', 'Others'];
        $statuses = ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'rejected'];
        $names = [
            ['Aarav Sharma', 'Full Stack Developer'],
            ['Priya Verma', 'UI/UX Designer'],
            ['Rohan Gupta', 'Digital Marketing Executive'],
            ['Ananya Singh', 'Product Manager'],
            ['Vikram Patel', 'Full Stack Developer'],
            ['Neha Joshi', 'Internship for Skill Upgrade'],
            ['Kabir Mehta', 'UI/UX Designer'],
            ['Isha Reddy', 'Full Stack Developer'],
            ['Arjun Nair', 'Product Manager'],
            ['Sana Khan', 'Digital Marketing Executive'],
            ['Dev Mishra', 'Internship for Skill Upgrade'],
            ['Meera Kapoor', 'Full Stack Developer'],
        ];

        foreach ($names as $i => [$name, $role]) {
            $opening = JobOpening::query()->where('title', $role)->first();
            JobApplication::query()->updateOrCreate(
                ['email' => strtolower(str_replace(' ', '.', $name)).'@example.com'],
                [
                    'job_opening_id' => $opening?->id,
                    'position' => $role,
                    'department' => $opening?->department_name,
                    'job_type' => str_contains($role, 'Internship') ? 'Internship' : 'Full-time',
                    'full_name' => $name,
                    'phone' => '98'.str_pad((string) (76543210 + $i), 8, '0', STR_PAD_LEFT),
                    'source' => $sources[$i % count($sources)],
                    'status' => $statuses[$i % count($statuses)],
                    'total_experience' => ($i % 5).' years',
                    'why_join' => 'Excited to contribute to KK Digital.',
                    'created_at' => Carbon::now()->subDays(29 - ($i * 2))->addHours($i),
                    'updated_at' => Carbon::now()->subDays(29 - ($i * 2))->addHours($i),
                ]
            );
        }

        $apps = JobApplication::query()->latest()->take(6)->get();
        foreach ($apps as $i => $app) {
            Interview::query()->updateOrCreate(
                ['candidate_name' => $app->full_name, 'position' => $app->position],
                [
                    'job_application_id' => $app->id,
                    'interviewer' => $i % 2 ? 'Prasenjit Mishra' : 'Divyanshu Mishra',
                    'scheduled_at' => Carbon::now()->addDays($i + 1)->setTime(11 + $i, 0),
                    'mode' => $i % 2 ? 'Online' : 'Onsite',
                    'status' => $i === 0 ? 'completed' : 'scheduled',
                    'notes' => 'Round '.($i + 1).' discussion',
                ]
            );
        }

        $employees = [
            ['name' => 'Yashi Sachan', 'email' => 'yashi@kkdigital.com', 'role_title' => 'Project Head', 'department_id' => $prd?->id, 'employee_code' => 'KK001', 'salary' => 55000],
            ['name' => 'Tapaswi Tiwari', 'email' => 'tapaswi@kkdigital.com', 'role_title' => 'Digital Marketing Head', 'department_id' => $mkt?->id, 'employee_code' => 'KK002', 'salary' => 48000],
            ['name' => 'Pranjal Ithapr', 'email' => 'pranjal@kkdigital.com', 'role_title' => 'Intern', 'department_id' => $eng?->id, 'employee_code' => 'KK003', 'salary' => 12000, 'employment_type' => 'Internship'],
            ['name' => 'Amit Yadav', 'email' => 'amit@kkdigital.com', 'role_title' => 'Backend Developer', 'department_id' => $eng?->id, 'employee_code' => 'KK005', 'salary' => 42000],
        ];
        foreach ($employees as $emp) {
            Employee::query()->updateOrCreate(
                ['email' => $emp['email']],
                $emp + ['phone' => '9000000000', 'join_date' => Carbon::now()->subMonths(6), 'status' => 'active', 'employment_type' => $emp['employment_type'] ?? 'Full-time']
            );
        }

        foreach (Employee::all() as $i => $emp) {
            AttendanceRecord::query()->updateOrCreate(
                ['employee_id' => $emp->id, 'date' => Carbon::today()->toDateString()],
                [
                    'check_in' => '09:'.str_pad((string) (10 + $i), 2, '0', STR_PAD_LEFT).':00',
                    'check_out' => '18:30:00',
                    'status' => $i === 3 ? 'late' : 'present',
                ]
            );

            if ($i < 2) {
                LeaveRequest::query()->updateOrCreate(
                    ['employee_id' => $emp->id, 'start_date' => Carbon::now()->addDays(7)->toDateString()],
                    [
                        'leave_type' => 'Casual',
                        'end_date' => Carbon::now()->addDays(8)->toDateString(),
                        'days' => 2,
                        'status' => $i === 0 ? 'pending' : 'approved',
                        'reason' => 'Personal work',
                    ]
                );
            }

            PayrollRecord::query()->updateOrCreate(
                ['employee_id' => $emp->id, 'month' => Carbon::now()->format('Y-m')],
                [
                    'basic' => $emp->salary ?? 30000,
                    'allowances' => 3000,
                    'deductions' => 1200,
                    'net_pay' => ((float) ($emp->salary ?? 30000)) + 3000 - 1200,
                    'status' => $i % 2 ? 'paid' : 'pending',
                ]
            );
        }

        Banner::query()->updateOrCreate(
            ['title' => 'Build your career with KK Digital'],
            [
                'subtitle' => 'Open roles across product, engineering and marketing',
                'cta_label' => 'View openings',
                'cta_url' => '/careers',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        Testimonial::query()->updateOrCreate(
            ['name' => 'Aarav Sharma'],
            [
                'role' => 'Full Stack Developer',
                'company' => 'KK Digital',
                'quote' => 'Great culture, real ownership and fast learning on live products.',
                'rating' => 5,
                'is_published' => true,
            ]
        );

        CmsPage::query()->updateOrCreate(
            ['slug' => 'life-at-kk'],
            [
                'title' => 'Life at KK Digital',
                'status' => 'published',
                'content' => 'We build digital products with clarity, craft and care.',
            ]
        );

        Announcement::query()->updateOrCreate(
            ['title' => 'Hiring week kickoff'],
            [
                'body' => 'We are accelerating interviews for engineering and design roles this week.',
                'audience' => 'All employees',
                'status' => 'published',
                'published_at' => now(),
            ]
        );

        SupportTicket::query()->updateOrCreate(
            ['ticket_no' => 'TKT-260815-101'],
            [
                'subject' => 'Unable to upload resume',
                'requester_name' => 'Neha Joshi',
                'requester_email' => 'neha.joshi@example.com',
                'priority' => 'high',
                'status' => 'open',
                'message' => 'Apply form throws an error on PDF upload.',
            ]
        );
        SupportTicket::query()->updateOrCreate(
            ['ticket_no' => 'TKT-260815-102'],
            [
                'subject' => 'Interview link missing',
                'requester_name' => 'Vikram Patel',
                'requester_email' => 'vikram.patel@example.com',
                'priority' => 'medium',
                'status' => 'pending',
                'message' => 'Did not receive Google Meet link for tomorrow.',
            ]
        );

        foreach ([
            ['name' => 'Super Admin', 'slug' => 'super-admin', 'users_count' => 1, 'permissions' => ['*']],
            ['name' => 'HR Manager', 'slug' => 'hr-manager', 'users_count' => 2, 'permissions' => ['hiring', 'employees']],
            ['name' => 'Recruiter', 'slug' => 'recruiter', 'users_count' => 3, 'permissions' => ['applications', 'interviews']],
        ] as $role) {
            AdminRole::query()->updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
