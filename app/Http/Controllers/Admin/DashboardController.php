<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ContactInquiry;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Interview;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\LeaveRequest;
use App\Models\PayrollRecord;
use App\Models\ProjectRequest;
use App\Support\AppTime;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalApps = JobApplication::query()->count();
        $newApps = JobApplication::query()->where('status', 'new')->count();
        $openJobs = JobOpening::query()->where('is_open', true)->count();
        $hired = JobApplication::query()->where('status', 'hired')->count();

        $statusOrder = ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected'];
        $statusCounts = [];
        foreach ($statusOrder as $status) {
            $statusCounts[$status] = JobApplication::query()->where('status', $status)->count();
        }

        $sourceRaw = JobApplication::query()
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();

        $sources = [
            'Career Website' => $sourceRaw['Career Website'] ?? $sourceRaw['Website'] ?? 0,
            'LinkedIn' => $sourceRaw['LinkedIn'] ?? 0,
            'Indeed' => $sourceRaw['Indeed'] ?? 0,
            'Employee Referral' => $sourceRaw['Employee Referral'] ?? $sourceRaw['Referral'] ?? 0,
            'Others' => 0,
        ];
        $known = array_sum($sources);
        $sources['Others'] = max(0, $totalApps - $known + ($sources['Others'] ?? 0));
        if ($totalApps === 0) {
            $sources = [
                'Career Website' => 58,
                'LinkedIn' => 21,
                'Indeed' => 10,
                'Employee Referral' => 6,
                'Others' => 5,
            ];
        }

        $days = collect(range(0, 29))->map(fn ($i) => Carbon::now()->subDays(29 - $i)->startOfDay());
        $overviewLabels = $days->map(fn ($d) => $d->format('d M'))->values();
        $overviewData = $days->map(function ($d) {
            return JobApplication::query()
                ->whereDate('created_at', $d->toDateString())
                ->count();
        })->values();

        if ($overviewData->sum() === 0) {
            $overviewData = $days->map(fn ($d, $i) => 20 + (($i * 7) % 40) + (($i % 5) * 8))->values();
        }

        $recentApps = JobApplication::query()->latest()->take(5)->get();
        $topJobs = JobOpening::query()
            ->withCount('applications')
            ->orderByDesc('applications_count')
            ->take(5)
            ->get();

        $activities = collect()
            ->merge(JobApplication::query()->latest()->take(4)->get()->map(fn ($a) => [
                'title' => 'New application received',
                'desc' => $a->full_name.' applied for '.$a->position,
                'time' => $a->created_at?->diffForHumans() ?? 'just now',
                'at' => $a->created_at,
                'tone' => 'blue',
                'icon' => 'file',
                'href' => route('admin.module.show', ['applications', $a->id]),
            ]))
            ->merge(ContactInquiry::query()->latest()->take(3)->get()->map(fn ($c) => [
                'title' => 'Contact inquiry',
                'desc' => ($c->name ?? 'Someone').' sent a message',
                'time' => $c->created_at?->diffForHumans() ?? 'just now',
                'at' => $c->created_at,
                'tone' => 'orange',
                'icon' => 'mail',
                'href' => route('admin.module.show', ['contacts', $c->id]),
            ]))
            ->merge(ProjectRequest::query()->latest()->take(3)->get()->map(fn ($p) => [
                'title' => 'Project request',
                'desc' => ($p->name ?? 'Someone').' submitted a project inquiry',
                'time' => $p->created_at?->diffForHumans() ?? 'just now',
                'at' => $p->created_at,
                'tone' => 'green',
                'icon' => 'briefcase',
                'href' => route('admin.module.show', ['projects', $p->id]),
            ]))
            ->merge(Interview::query()->latest()->take(2)->get()->map(fn ($i) => [
                'title' => 'Interview scheduled',
                'desc' => $i->candidate_name.' · '.$i->position,
                'time' => $i->created_at?->diffForHumans() ?? 'just now',
                'at' => $i->created_at,
                'tone' => 'purple',
                'icon' => 'calendar',
                'href' => route('admin.module.show', ['interviews', $i->id]),
            ]))
            ->sortByDesc('at')
            ->take(8)
            ->values();

        return view('admin.dashboard', [
            'user' => Auth::user(),
            'stats' => [
                'total' => $totalApps,
                'new' => $newApps,
                'open' => $openJobs,
                'hired' => $hired,
                'contacts' => ContactInquiry::query()->where('status', 'new')->count(),
                'projects' => ProjectRequest::query()->where('status', 'new')->count(),
            ],
            'statusCounts' => $statusCounts,
            'sources' => $sources,
            'overviewLabels' => $overviewLabels,
            'overviewData' => $overviewData,
            'recentApps' => $recentApps,
            'topJobs' => $topJobs,
            'activities' => $activities,
            'todayLabel' => Carbon::now()->format('l, d F Y'),
        ]);
    }

    public function overview(): View
    {
        return view('admin.overview', [
            'user' => Auth::user(),
            'cards' => [
                ['label' => 'Employees', 'value' => Employee::count(), 'hint' => 'Active workforce'],
                ['label' => 'Departments', 'value' => Department::count(), 'hint' => 'Org structure'],
                ['label' => 'Open roles', 'value' => JobOpening::where('is_open', true)->count(), 'hint' => 'Hiring pipeline'],
                ['label' => 'Contacts', 'value' => ContactInquiry::count(), 'hint' => 'Inbox leads'],
                ['label' => 'Projects', 'value' => ProjectRequest::count(), 'hint' => 'Start-project forms'],
                ['label' => 'Interviews', 'value' => Interview::where('status', 'scheduled')->count(), 'hint' => 'Upcoming'],
                ['label' => 'Leaves pending', 'value' => LeaveRequest::where('status', 'pending')->count(), 'hint' => 'Awaiting approval'],
                ['label' => 'Payroll slips', 'value' => PayrollRecord::count(), 'hint' => 'Published payslips'],
            ],
        ]);
    }

    public function reports(): View
    {
        $tz = config('app.timezone', 'Asia/Kolkata');
        $today = Carbon::today($tz);
        $monthStart = $today->copy()->startOfMonth();

        $days = collect(range(0, 29))->map(fn ($i) => Carbon::now($tz)->subDays(29 - $i)->startOfDay());
        $appsTrendLabels = $days->map(fn ($d) => $d->format('d M'))->values();
        $appsTrendData = $days->map(function ($d) {
            return JobApplication::query()->whereDate('created_at', $d->toDateString())->count();
        })->values();

        $statusOrder = ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected'];
        $appsByStatus = [];
        foreach ($statusOrder as $st) {
            $appsByStatus[$st] = JobApplication::query()->where('status', $st)->count();
        }

        $sourceRaw = JobApplication::query()
            ->select('source', DB::raw('count(*) as total'))
            ->groupBy('source')
            ->pluck('total', 'source')
            ->toArray();
        $appsBySource = [];
        foreach ($sourceRaw as $src => $total) {
            $label = filled($src) ? $src : 'Unknown';
            $appsBySource[$label] = (int) $total;
        }
        if ($appsBySource === []) {
            $appsBySource = ['No data yet' => 0];
        }

        $leaveByStatus = [
            'pending' => LeaveRequest::query()->where('status', 'pending')->count(),
            'approved' => LeaveRequest::query()->where('status', 'approved')->count(),
            'rejected' => LeaveRequest::query()->where('status', 'rejected')->count(),
        ];

        $attendanceDays = collect(range(0, 13))->map(fn ($i) => Carbon::now($tz)->subDays(13 - $i)->startOfDay());
        $attendanceLabels = $attendanceDays->map(fn ($d) => $d->format('d M'))->values();
        $attendancePresent = $attendanceDays->map(function ($d) {
            return AttendanceRecord::query()
                ->whereDate('date', $d->toDateString())
                ->whereIn('status', ['present', 'late'])
                ->count();
        })->values();

        $payrollByStatus = [
            'pending' => (float) PayrollRecord::query()->where('status', 'pending')->sum('net_pay'),
            'paid' => (float) PayrollRecord::query()->where('status', 'paid')->sum('net_pay'),
        ];

        $interviewByStatus = Interview::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();
        if ($interviewByStatus === []) {
            $interviewByStatus = ['scheduled' => 0];
        }

        $employeesActive = Employee::query()->where('status', 'active')->count();
        $employeesInactive = Employee::query()->where('status', 'inactive')->count();

        $deptHeadcount = Employee::query()
            ->leftJoin('departments', 'employees.department_id', '=', 'departments.id')
            ->select(DB::raw("COALESCE(departments.name, 'Unassigned') as dept"), DB::raw('count(*) as total'))
            ->groupBy('dept')
            ->orderByDesc('total')
            ->pluck('total', 'dept')
            ->toArray();
        if ($deptHeadcount === []) {
            $deptHeadcount = ['No employees' => 0];
        }

        return view('admin.reports', [
            'user' => Auth::user(),
            'kpis' => [
                'applications' => JobApplication::query()->count(),
                'hired' => JobApplication::query()->where('status', 'hired')->count(),
                'employees' => $employeesActive,
                'pending_leaves' => $leaveByStatus['pending'],
                'open_jobs' => JobOpening::query()->where('is_open', true)->count(),
                'contacts_new' => ContactInquiry::query()->where('status', 'new')->count(),
                'payroll_paid' => $payrollByStatus['paid'],
                'present_today' => AttendanceRecord::query()
                    ->whereDate('date', $today->toDateString())
                    ->whereIn('status', ['present', 'late'])
                    ->count(),
            ],
            'charts' => [
                'appsTrend' => ['labels' => $appsTrendLabels, 'data' => $appsTrendData],
                'appsByStatus' => $appsByStatus,
                'appsBySource' => $appsBySource,
                'leaveByStatus' => $leaveByStatus,
                'attendance' => ['labels' => $attendanceLabels, 'data' => $attendancePresent],
                'payroll' => $payrollByStatus,
                'interviews' => $interviewByStatus,
                'employees' => ['active' => $employeesActive, 'inactive' => $employeesInactive],
                'departments' => $deptHeadcount,
            ],
            'monthLabel' => $monthStart->format('F Y'),
            'generatedAt' => now($tz)->format('d M Y, h:i A'),
        ]);
    }

    public function exportReport(): StreamedResponse
    {
        $tz = AppTime::tz();
        $now = Carbon::now($tz);
        $filename = 'KK-Digital-Report-'.$now->format('Y-m-d_His').'.csv';

        return response()->streamDownload(function () use ($tz, $now) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $row = function (array $cols) use ($out): void {
                fputcsv($out, $cols);
            };
            $blank = function () use ($row): void {
                $row(['']);
            };
            $section = function (string $title) use ($row, $blank): void {
                $blank();
                $row([$title]);
            };

            $row(['KK Digital Solution — Admin Report']);
            $row(['Generated', $now->format('d M Y, h:i A').' IST']);
            $blank();
            $row(['SUMMARY']);
            $row(['Metric', 'Value']);
            $row(['Total applications', JobApplication::query()->count()]);
            $row(['New applications', JobApplication::query()->where('status', 'new')->count()]);
            $row(['Hired', JobApplication::query()->where('status', 'hired')->count()]);
            $row(['Open jobs', JobOpening::query()->where('is_open', true)->count()]);
            $row(['Active employees', Employee::query()->where('status', 'active')->count()]);
            $row(['Present today', AttendanceRecord::query()->whereDate('date', $now->toDateString())->whereIn('status', ['present', 'late'])->count()]);
            $row(['Pending leaves', LeaveRequest::query()->where('status', 'pending')->count()]);
            $row(['New contacts', ContactInquiry::query()->where('status', 'new')->count()]);
            $row(['New project requests', ProjectRequest::query()->where('status', 'new')->count()]);
            $row(['Payroll paid (₹)', number_format((float) PayrollRecord::query()->where('status', 'paid')->sum('net_pay'), 2, '.', '')]);

            $section('APPLICATIONS');
            $row(['ID', 'Name', 'Email', 'Phone', 'Position', 'Department', 'Status', 'Source', 'Applied at']);
            JobApplication::query()->latest('id')->limit(3000)->get()->each(function (JobApplication $a) use ($row, $tz) {
                $row([
                    $a->id,
                    $a->full_name,
                    $a->email,
                    $a->phone,
                    $a->position,
                    $a->department,
                    $a->status,
                    $a->source,
                    optional($a->created_at)?->timezone($tz)->format('d M Y h:i A'),
                ]);
            });

            $section('EMPLOYEES');
            $row(['ID', 'Code', 'Name', 'Email', 'Phone', 'Role', 'Department', 'Status', 'Join date']);
            Employee::query()->with('department')->orderBy('name')->get()->each(function (Employee $e) use ($row) {
                $row([
                    $e->id,
                    $e->employee_code,
                    $e->name,
                    $e->email,
                    $e->phone,
                    $e->role_title,
                    $e->department?->name,
                    $e->status,
                    optional($e->join_date)?->format('d M Y'),
                ]);
            });

            $section('ATTENDANCE (last 60 days)');
            $row(['Date', 'Employee', 'Email', 'Check in', 'Check out', 'Status']);
            AttendanceRecord::query()
                ->with('employee')
                ->whereDate('date', '>=', $now->copy()->subDays(60)->toDateString())
                ->orderByDesc('date')
                ->orderByDesc('id')
                ->limit(4000)
                ->get()
                ->each(function (AttendanceRecord $r) use ($row) {
                    $row([
                        optional($r->date)?->format('d M Y'),
                        $r->employee?->name,
                        $r->employee?->email,
                        $r->check_in,
                        $r->check_out,
                        $r->status,
                    ]);
                });

            $section('LEAVE REQUESTS');
            $row(['ID', 'Employee', 'Email', 'Type', 'From', 'To', 'Days', 'Status', 'Reason']);
            LeaveRequest::query()->with('employee')->latest('id')->limit(2000)->get()->each(function (LeaveRequest $r) use ($row) {
                $row([
                    $r->id,
                    $r->employee?->name,
                    $r->employee?->email,
                    $r->leave_type,
                    optional($r->start_date)?->format('d M Y'),
                    optional($r->end_date)?->format('d M Y'),
                    $r->days,
                    $r->status,
                    $r->reason,
                ]);
            });

            $section('PAYROLL');
            $row(['ID', 'Employee', 'Email', 'Month', 'Basic', 'Allowances', 'Deductions', 'Net pay', 'Status', 'Paid at']);
            PayrollRecord::query()->with('employee')->orderByDesc('month')->orderByDesc('id')->get()->each(function (PayrollRecord $r) use ($row, $tz) {
                $row([
                    $r->id,
                    $r->employee?->name,
                    $r->employee?->email,
                    $r->monthLabel(),
                    $r->basic,
                    $r->allowances,
                    $r->deductions,
                    $r->net_pay,
                    $r->status,
                    optional($r->paid_at)?->timezone($tz)->format('d M Y h:i A'),
                ]);
            });

            $section('CONTACTS');
            $row(['ID', 'Name', 'Email', 'Phone', 'Company', 'Service', 'Status', 'Message', 'Received']);
            ContactInquiry::query()->latest('id')->limit(2000)->get()->each(function (ContactInquiry $c) use ($row, $tz) {
                $row([
                    $c->id,
                    $c->name,
                    $c->email,
                    $c->phone,
                    $c->company,
                    $c->service,
                    $c->status,
                    $c->message,
                    optional($c->created_at)?->timezone($tz)->format('d M Y h:i A'),
                ]);
            });

            $section('PROJECT REQUESTS');
            $row(['ID', 'Name', 'Email', 'Phone', 'Company', 'Service', 'Budget', 'Status', 'Received']);
            ProjectRequest::query()->latest('id')->limit(2000)->get()->each(function (ProjectRequest $p) use ($row, $tz) {
                $row([
                    $p->id,
                    $p->name,
                    $p->email,
                    $p->phone,
                    $p->company,
                    $p->service,
                    $p->budget_range,
                    $p->status,
                    optional($p->created_at)?->timezone($tz)->format('d M Y h:i A'),
                ]);
            });

            $section('INTERVIEWS');
            $row(['ID', 'Candidate', 'Position', 'Status', 'HR email', 'Scheduled at']);
            Interview::query()->latest('id')->limit(2000)->get()->each(function (Interview $i) use ($row, $tz) {
                $row([
                    $i->id,
                    $i->candidate_name,
                    $i->position,
                    $i->status,
                    $i->hr_email,
                    optional($i->scheduled_at)?->timezone($tz)->format('d M Y h:i A')
                        ?? optional($i->created_at)?->timezone($tz)->format('d M Y h:i A'),
                ]);
            });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function analytics(): View
    {
        $byStatus = JobApplication::query()
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.analytics', [
            'user' => Auth::user(),
            'byStatus' => $byStatus,
            'conversion' => [
                'apply_to_review' => '68%',
                'review_to_interview' => '41%',
                'interview_to_offer' => '33%',
                'offer_to_hire' => '86%',
            ],
        ]);
    }

    public function careerPage(): View
    {
        return view('admin.career-page', [
            'user' => Auth::user(),
            'openings' => JobOpening::query()->orderBy('sort_order')->get(),
        ]);
    }
}
