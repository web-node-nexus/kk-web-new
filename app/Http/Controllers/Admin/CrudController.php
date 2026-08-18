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
use App\Models\WorkTaskAssignee;
use App\Services\ApplicationNotifier;
use App\Services\EmployeeAccountService;
use App\Services\InterviewHrNotifier;
use App\Support\Ajax;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrudController extends Controller
{
    /** @return array<string, array<string, mixed>> */
    protected function modules(): array
    {
        return [
            'applications' => [
                'title' => 'Job Applications',
                'model' => JobApplication::class,
                'subtitle' => 'Select applications → Review → Shortlist → Hire, Offer Internship, or Reject',
                'search' => ['full_name', 'email', 'position', 'phone'],
                'status_field' => 'status',
                'statuses' => ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected'],
                'workflow' => true,
                'columns' => [
                    ['key' => 'full_name', 'label' => 'Candidate'],
                    ['key' => 'position', 'label' => 'Role'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'source', 'label' => 'Source'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'created_at', 'label' => 'Applied', 'type' => 'date'],
                ],
                'fields' => [
                    ['name' => 'full_name', 'label' => 'Full name', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone'],
                    ['name' => 'position', 'label' => 'Position', 'required' => true],
                    ['name' => 'department', 'label' => 'Department'],
                    ['name' => 'job_type', 'label' => 'Job type'],
                    ['name' => 'qualification', 'label' => 'Qualification'],
                    ['name' => 'total_experience', 'label' => 'Experience'],
                    ['name' => 'source', 'label' => 'Source'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected']],
                    ['name' => 'why_join', 'label' => 'Why join / Notes', 'type' => 'textarea', 'full' => true],
                    ['name' => 'resume_path', 'label' => 'Resume', 'type' => 'file', 'show_only' => true],
                    ['name' => 'cover_letter_path', 'label' => 'Cover letter', 'type' => 'file', 'show_only' => true],
                ],
                'filter_internship' => false,
            ],
            'internships' => [
                'title' => 'Internships',
                'model' => JobOpening::class,
                'subtitle' => 'Add internship roles (admin only — not shown as a separate website page)',
                'search' => ['title', 'location', 'department_name', 'employment_type'],
                'scope' => 'internship_openings',
                'columns' => [
                    ['key' => 'title', 'label' => 'Internship role'],
                    ['key' => 'department_name', 'label' => 'Department'],
                    ['key' => 'location', 'label' => 'Location'],
                    ['key' => 'openings_count', 'label' => 'Seats'],
                    ['key' => 'is_open', 'label' => 'Website', 'type' => 'bool_open'],
                    ['key' => 'sort_order', 'label' => 'Order'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Internship title', 'required' => true],
                    ['name' => 'department_name', 'label' => 'Department'],
                    ['name' => 'employment_type', 'label' => 'Type', 'default' => 'Internship'],
                    ['name' => 'location', 'label' => 'Location', 'default' => 'India'],
                    ['name' => 'openings_count', 'label' => 'Openings / seats', 'type' => 'number', 'default' => 1],
                    ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
                    ['name' => 'is_open', 'label' => 'Show on website (must be checked to appear live)', 'type' => 'checkbox', 'default' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'candidates' => [
                'title' => 'Candidates',
                'model' => JobApplication::class,
                'subtitle' => 'Talent pool across all roles',
                'search' => ['full_name', 'email', 'phone', 'position'],
                'columns' => [
                    ['key' => 'full_name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'phone', 'label' => 'Phone'],
                    ['key' => 'position', 'label' => 'Latest role'],
                    ['key' => 'total_experience', 'label' => 'Experience'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'full_name', 'label' => 'Full name', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone'],
                    ['name' => 'position', 'label' => 'Position'],
                    ['name' => 'total_experience', 'label' => 'Experience'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['new', 'reviewed', 'shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected']],
                ],
            ],
            'interviews' => [
                'title' => 'Interviews',
                'model' => Interview::class,
                'subtitle' => 'Schedule interviews and send assignment details + link to HR',
                'search' => ['candidate_name', 'position', 'interviewer', 'notes'],
                'status_field' => 'status',
                'statuses' => ['scheduled', 'approved', 'completed', 'cancelled'],
                'with' => ['application'],
                'hr_notify' => true,
                'columns' => [
                    ['key' => 'candidate_name', 'label' => 'Candidate'],
                    ['key' => 'application.email', 'label' => 'Email'],
                    ['key' => 'application.phone', 'label' => 'Phone'],
                    ['key' => 'position', 'label' => 'Position'],
                    ['key' => 'scheduled_at', 'label' => 'When', 'type' => 'datetime'],
                    ['key' => 'mode', 'label' => 'Mode'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'hr_notified_at', 'label' => 'Sent to HR', 'type' => 'datetime'],
                ],
                'fields' => [
                    ['name' => 'candidate_name', 'label' => 'Candidate', 'required' => true],
                    ['name' => 'position', 'label' => 'Position'],
                    ['name' => 'interviewer', 'label' => 'Interviewer / HR name'],
                    ['name' => 'scheduled_at', 'label' => 'Interview date & time', 'type' => 'datetime-local', 'required' => true],
                    ['name' => 'mode', 'label' => 'Mode', 'type' => 'select', 'options' => ['Online', 'Online (Video Call)', 'Onsite', 'Approved']],
                    ['name' => 'meeting_link', 'label' => 'Meeting / join link (Zoom/Meet)'],
                    ['name' => 'hr_email', 'label' => 'Assign HR', 'type' => 'hr_picker', 'hint' => 'Search and select HR from Employees list'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['scheduled', 'approved', 'completed', 'cancelled']],
                    ['name' => 'notes', 'label' => 'Notes / candidate details', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'jobs' => [
                'title' => 'Job Openings',
                'model' => JobOpening::class,
                'subtitle' => 'Add/edit full-time & contract roles — Open ones appear on Careers',
                'search' => ['title', 'employment_type', 'location', 'department_name'],
                'columns' => [
                    ['key' => 'title', 'label' => 'Role'],
                    ['key' => 'department_name', 'label' => 'Department'],
                    ['key' => 'employment_type', 'label' => 'Type'],
                    ['key' => 'location', 'label' => 'Location'],
                    ['key' => 'is_open', 'label' => 'Website', 'type' => 'bool_open'],
                    ['key' => 'sort_order', 'label' => 'Order'],
                ],
                'fields' => [
                    ['name' => 'title', 'label' => 'Title', 'required' => true],
                    ['name' => 'department_name', 'label' => 'Department'],
                    ['name' => 'employment_type', 'label' => 'Employment type'],
                    ['name' => 'location', 'label' => 'Location'],
                    ['name' => 'openings_count', 'label' => 'Openings', 'type' => 'number'],
                    ['name' => 'sort_order', 'label' => 'Sort order', 'type' => 'number'],
                    ['name' => 'is_open', 'label' => 'Show on website (must be checked to appear live)', 'type' => 'checkbox', 'default' => true],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'departments' => [
                'title' => 'Departments',
                'model' => Department::class,
                'subtitle' => 'Organization departments',
                'search' => ['name', 'code', 'head_name'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Department'],
                    ['key' => 'code', 'label' => 'Code'],
                    ['key' => 'head_name', 'label' => 'Head'],
                    ['key' => 'is_active', 'label' => 'Active', 'type' => 'bool'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'required' => true],
                    ['name' => 'code', 'label' => 'Code'],
                    ['name' => 'head_name', 'label' => 'Department head'],
                    ['name' => 'is_active', 'label' => 'Active', 'type' => 'checkbox'],
                    ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'employees' => [
                'title' => 'Employees',
                'model' => Employee::class,
                'subtitle' => 'Add employee with email & password — panel login + welcome email sent automatically',
                'search' => ['name', 'email', 'phone', 'role_title', 'employee_code'],
                'status_field' => 'status',
                'statuses' => ['active', 'inactive'],
                'with' => ['department'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Employee'],
                    ['key' => 'role_title', 'label' => 'Role'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'employment_type', 'label' => 'Emp type'],
                    ['key' => 'department.name', 'label' => 'Department'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'required' => true],
                    ['name' => 'email', 'label' => 'Login email (panel)', 'type' => 'email', 'required' => true],
                    ['name' => 'panel_password', 'label' => 'Panel password *', 'type' => 'text', 'virtual' => true, 'required_on_create' => true, 'hint' => 'Yahi password employee ko mail me jayega. Minimum 6 characters.'],
                    ['name' => 'phone', 'label' => 'Phone'],
                    ['name' => 'photo_path', 'label' => 'Employee image', 'type' => 'file'],
                    ['name' => 'employee_code', 'label' => 'Employee code'],
                    ['name' => 'role_title', 'label' => 'Role title'],
                    ['name' => 'employment_type', 'label' => 'Emp type', 'type' => 'select', 'options' => Employee::EMP_TYPES, 'required' => true],
                    ['name' => 'job_type', 'label' => 'Job type', 'type' => 'select', 'options' => Employee::JOB_TYPES, 'required' => true],
                    ['name' => 'department_name', 'label' => 'Department', 'type' => 'select', 'options' => Employee::DEPARTMENTS, 'virtual' => true, 'required' => true],
                    ['name' => 'join_date', 'label' => 'Join date', 'type' => 'date'],
                    ['name' => 'salary', 'label' => 'Salary', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active', 'inactive']],
                ],
            ],
            'attendance' => [
                'title' => 'Attendance',
                'model' => AttendanceRecord::class,
                'subtitle' => 'Daily attendance tracking',
                'with' => ['employee'],
                'search' => [],
                'status_field' => 'status',
                'statuses' => ['present', 'absent', 'late', 'leave'],
                'columns' => [
                    ['key' => 'employee.name', 'label' => 'Employee'],
                    ['key' => 'date', 'label' => 'Date', 'type' => 'date'],
                    ['key' => 'check_in', 'label' => 'Check in'],
                    ['key' => 'check_out', 'label' => 'Check out'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'employee_id', 'label' => 'Employee ID', 'type' => 'number', 'required' => true],
                    ['name' => 'date', 'label' => 'Date', 'type' => 'date', 'required' => true],
                    ['name' => 'check_in', 'label' => 'Check in', 'type' => 'time'],
                    ['name' => 'check_out', 'label' => 'Check out', 'type' => 'time'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['present', 'absent', 'late', 'leave']],
                ],
            ],
            'leaves' => [
                'title' => 'Leave Requests',
                'model' => LeaveRequest::class,
                'subtitle' => 'Approve or reject employee leaves',
                'with' => ['employee'],
                'status_field' => 'status',
                'statuses' => ['pending', 'approved', 'rejected'],
                'columns' => [
                    ['key' => 'employee.name', 'label' => 'Employee'],
                    ['key' => 'leave_type', 'label' => 'Type'],
                    ['key' => 'start_date', 'label' => 'From', 'type' => 'date'],
                    ['key' => 'end_date', 'label' => 'To', 'type' => 'date'],
                    ['key' => 'days', 'label' => 'Days'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'employee_id', 'label' => 'Employee ID', 'type' => 'number', 'required' => true],
                    ['name' => 'leave_type', 'label' => 'Leave type'],
                    ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'required' => true],
                    ['name' => 'end_date', 'label' => 'End date', 'type' => 'date', 'required' => true],
                    ['name' => 'days', 'label' => 'Days', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending', 'approved', 'rejected']],
                    ['name' => 'reason', 'label' => 'Reason', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'payroll' => [
                'title' => 'Payroll',
                'model' => PayrollRecord::class,
                'subtitle' => 'Monthly payroll records',
                'with' => ['employee'],
                'status_field' => 'status',
                'statuses' => ['pending', 'paid'],
                'columns' => [
                    ['key' => 'employee.name', 'label' => 'Employee'],
                    ['key' => 'month', 'label' => 'Month'],
                    ['key' => 'basic', 'label' => 'Basic'],
                    ['key' => 'net_pay', 'label' => 'Net pay'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                ],
                'fields' => [
                    ['name' => 'employee_id', 'label' => 'Employee ID', 'type' => 'number', 'required' => true],
                    ['name' => 'month', 'label' => 'Month (YYYY-MM)', 'required' => true],
                    ['name' => 'basic', 'label' => 'Basic', 'type' => 'number'],
                    ['name' => 'allowances', 'label' => 'Allowances', 'type' => 'number'],
                    ['name' => 'deductions', 'label' => 'Deductions', 'type' => 'number'],
                    ['name' => 'net_pay', 'label' => 'Net pay', 'type' => 'number'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['pending', 'paid']],
                ],
            ],
            'contacts' => [
                'title' => 'Contact Inquiries',
                'model' => ContactInquiry::class,
                'subtitle' => 'Messages from the website contact form — arrive instantly here',
                'search' => ['name', 'email', 'company', 'service', 'message'],
                'status_field' => 'status',
                'statuses' => ['new', 'reviewed', 'closed'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'company', 'label' => 'Company'],
                    ['key' => 'service', 'label' => 'Service'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'created_at', 'label' => 'Received', 'type' => 'date'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone'],
                    ['name' => 'company', 'label' => 'Company'],
                    ['name' => 'service', 'label' => 'Service'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['new', 'reviewed', 'closed']],
                    ['name' => 'message', 'label' => 'Message', 'type' => 'textarea', 'full' => true],
                ],
            ],
            'projects' => [
                'title' => 'Project Requests',
                'model' => ProjectRequest::class,
                'subtitle' => 'Start Project form submissions from the website',
                'search' => ['name', 'email', 'company', 'service', 'description'],
                'status_field' => 'status',
                'statuses' => ['new', 'reviewed', 'closed'],
                'columns' => [
                    ['key' => 'name', 'label' => 'Name'],
                    ['key' => 'email', 'label' => 'Email'],
                    ['key' => 'service', 'label' => 'Service'],
                    ['key' => 'budget_range', 'label' => 'Budget'],
                    ['key' => 'status', 'label' => 'Status', 'type' => 'status'],
                    ['key' => 'created_at', 'label' => 'Received', 'type' => 'date'],
                ],
                'fields' => [
                    ['name' => 'name', 'label' => 'Name', 'required' => true],
                    ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
                    ['name' => 'phone', 'label' => 'Phone'],
                    ['name' => 'company', 'label' => 'Company'],
                    ['name' => 'service', 'label' => 'Service'],
                    ['name' => 'budget_range', 'label' => 'Budget'],
                    ['name' => 'timeline', 'label' => 'Timeline'],
                    ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['new', 'reviewed', 'closed']],
                    ['name' => 'description', 'label' => 'Project details', 'type' => 'textarea', 'full' => true],
                    ['name' => 'attachment_path', 'label' => 'Attachment', 'type' => 'file', 'show_only' => true],
                ],
            ],
        ];
    }

    protected function module(string $module): array
    {
        $modules = $this->modules();
        abort_unless(isset($modules[$module]), 404);

        return $modules[$module] + ['key' => $module];
    }

    protected function queryFor(array $mod)
    {
        /** @var Model $model */
        $model = $mod['model'];
        $query = $model::query();

        if (! empty($mod['with'])) {
            $query->with($mod['with']);
        }

        if (($mod['scope'] ?? null) === 'internship_openings') {
            $query->where(function ($q) {
                $q->where('employment_type', 'like', '%intern%')
                    ->orWhere('title', 'like', '%intern%');
            });
        }

        return $query;
    }

    public function index(Request $request, string $module): View
    {
        $mod = $this->module($module);
        $query = $this->queryFor($mod)->latest();

        if (! empty($mod['status_field']) && $request->filled('status') && $request->string('status') !== 'all') {
            $query->where($mod['status_field'], $request->string('status'));
        }

        if ($request->filled('q') && ! empty($mod['search'])) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($mod, $term) {
                foreach ($mod['search'] as $i => $col) {
                    $i === 0 ? $q->where($col, 'like', $term) : $q->orWhere($col, 'like', $term);
                }
            });
        }

        $counts = [];
        if (! empty($mod['statuses']) && ! empty($mod['status_field'])) {
            $base = $this->queryFor($mod);
            $counts['all'] = (clone $base)->count();
            foreach ($mod['statuses'] as $status) {
                $counts[$status] = (clone $base)->where($mod['status_field'], $status)->count();
            }
        }

        return view('admin.crud.index', [
            'user' => Auth::user(),
            'mod' => $mod,
            'items' => $query->paginate(12)->withQueryString(),
            'filters' => [
                'status' => $request->string('status')->toString() ?: 'all',
                'q' => $request->string('q')->toString(),
            ],
            'counts' => $counts,
            'hrEmployees' => ! empty($mod['hr_notify'])
                ? Employee::query()
                    ->where('status', 'active')
                    ->orderBy('name')
                    ->get(['id', 'name', 'email', 'role_title', 'employee_code'])
                : collect(),
        ]);
    }

    public function searchHrEmployees(Request $request)
    {
        $term = trim((string) $request->string('q'));
        $query = Employee::query()
            ->where('status', 'active');

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($q) use ($like) {
                $q->where('name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('role_title', 'like', $like)
                    ->orWhere('employee_code', 'like', $like)
                    ->orWhere('phone', 'like', $like);
            });
        }

        // Prefer HR-titled roles first when browsing/searching large lists
        $query->orderByRaw("CASE WHEN role_title LIKE '%HR%' OR role_title LIKE '%Human Resource%' THEN 0 ELSE 1 END")
            ->orderBy('name');

        return response()->json(
            $query->limit(40)->get(['id', 'name', 'email', 'role_title', 'employee_code'])
                ->map(fn (Employee $e) => [
                    'id' => $e->id,
                    'name' => $e->name,
                    'email' => $e->email,
                    'role_title' => $e->role_title,
                    'employee_code' => $e->employee_code,
                    'label' => trim($e->name.' — '.$e->email.($e->role_title ? ' ('.$e->role_title.')' : '')),
                ])
        );
    }

    public function create(string $module): View
    {
        $mod = $this->module($module);

        return view('admin.crud.form', [
            'user' => Auth::user(),
            'mod' => $mod,
            'item' => null,
            'mode' => 'create',
            'hrEmployees' => $module === 'interviews'
                ? Employee::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'email', 'role_title', 'employee_code'])
                : collect(),
        ]);
    }

    public function store(Request $request, string $module): RedirectResponse|JsonResponse
    {
        $mod = $this->module($module);
        $data = $this->validated($request, $mod, 'create');
        // Always read password from request (browser sometimes skips password-type fields)
        $panelPassword = trim((string) $request->input('panel_password', $data['panel_password'] ?? ''));
        unset($data['panel_password'], $data['department_name'], $data['photo_path']);

        if ($module === 'employees' && $panelPassword === '') {
            return Ajax::fail($request, 'Panel password is required (min 6 characters).', ['panel_password' => ['Panel password is required (min 6 characters).']]);
        }
        if ($module === 'employees' && strlen($panelPassword) < 6) {
            return Ajax::fail($request, 'Panel password must be at least 6 characters.', ['panel_password' => ['Panel password must be at least 6 characters.']]);
        }

        if (in_array($module, ['jobs', 'internships'], true)) {
            // Form sends hidden 0 + checkbox 1; "1" means show on website
            $data['is_open'] = (string) $request->input('is_open', '1') === '1';
            $data['sort_order'] = $data['sort_order'] ?? ((int) JobOpening::query()->max('sort_order') + 1);
            $data['openings_count'] = $data['openings_count'] ?? 1;
            $data['location'] = filled($data['location'] ?? null) ? $data['location'] : 'India';
            $data['title'] = $data['title'] ?? ($module === 'internships' ? 'Untitled internship' : 'Untitled role');
            if ($module === 'internships') {
                $data['employment_type'] = 'Internship';
            } else {
                $data['employment_type'] = $data['employment_type'] ?: 'Full-time';
            }
            if (empty($data['description'])) {
                $data['description'] = $data['title'];
            }
        }
        if ($module === 'employees') {
            $data['email'] = strtolower(trim((string) ($data['email'] ?? '')));
            $data['status'] = $data['status'] ?? 'active';
            $data['employment_type'] = $data['employment_type'] ?: 'Full-time';
            $data['job_type'] = $data['job_type'] ?: 'Permanent';
            $data['department_id'] = $this->departmentIdFromName((string) $request->input('department_name', ''));
            if (! filled($data['salary'] ?? null)) {
                $data['salary'] = null;
            }
        }

        /** @var Model $model */
        $model = $mod['model'];

        try {
            $item = $model::query()->create($data);
        } catch (QueryException $e) {
            if ($module === 'employees' && str_contains($e->getMessage(), 'employees_email_unique')) {
                return Ajax::fail($request, 'This email is already registered as an employee.', ['email' => ['This email is already registered as an employee. Open that employee and edit, or use a different email.']]);
            }
            throw $e;
        }

        if ($module === 'employees') {
            $this->storeEmployeePhoto($request, $item);
        }

        $extra = '';
        if ($module === 'jobs' && ! empty($data['is_open'])) {
            $extra = ' It is now live on the Careers page.';
        }
        if ($module === 'internships' && ! empty($data['is_open'])) {
            $extra = ' Saved in admin Internships.';
        }
        if ($module === 'employees') {
            $result = EmployeeAccountService::provision(
                $item->load('department'),
                $panelPassword,
                true,
                true,
            );
            if (! empty($result['error']) && $result['error'] === 'email_is_admin') {
                $extra = ' Warning: this email belongs to an admin account — employee panel was not created.';
            } elseif ($result['user'] ?? null) {
                $extra = ' Panel created for '.$item->email.'.';
                $extra .= ($result['mailed'] ?? false)
                    ? ' Welcome email with login password sent.'
                    : ' (Email could not be sent — check mail settings.)';
            } else {
                $extra = ' Could not create panel login.';
            }
        }

        return Ajax::ok($request, $mod['title'].' created successfully.'.$extra, route('admin.module.index', $module));
    }

    public function edit(string $module, int $id): View
    {
        $mod = $this->module($module);
        $item = $this->queryFor($mod)->findOrFail($id);

        return view('admin.crud.form', [
            'user' => Auth::user(),
            'mod' => $mod,
            'item' => $item,
            'mode' => 'edit',
            'hrEmployees' => $module === 'interviews'
                ? Employee::query()->where('status', 'active')->orderBy('name')->get(['id', 'name', 'email', 'role_title', 'employee_code'])
                : collect(),
        ]);
    }

    public function show(string $module, int $id): View
    {
        $mod = $this->module($module);
        $item = $this->queryFor($mod)->findOrFail($id);

        if ($module === 'applications') {
            return view('admin.applications.show', [
                'user' => Auth::user(),
                'mod' => $mod,
                'item' => $item,
                'pipeline' => [
                    'new' => 'New',
                    'reviewed' => 'Reviewed',
                    'shortlisted' => 'Shortlisted',
                    'interview' => 'Interview',
                    'offered' => 'Offered',
                    'hired' => 'Approved / Hired',
                    'internship_offered' => 'Internship Offer',
                    'rejected' => 'Rejected',
                ],
            ]);
        }

        if ($module === 'employees') {
            return $this->showEmployee($item);
        }

        return view('admin.crud.show', [
            'user' => Auth::user(),
            'mod' => $mod,
            'item' => $item,
        ]);
    }

    public function interviewNotifyHr(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'hr_email' => ['required', 'email'],
            'meeting_link' => ['nullable', 'string', 'max:500'],
        ]);

        $interview = Interview::query()->with('application')->findOrFail($id);
        $result = InterviewHrNotifier::notify(
            $interview,
            $data['hr_email'] ?? null,
            $data['meeting_link'] ?? null,
        );

        if (! ($result['ok'] ?? false)) {
            return back()->withErrors(['hr' => 'Could not assign/email HR: '.($result['error'] ?? 'unknown error')]);
        }

        $msg = 'Assigned to HR panel';
        if ($result['assigned'] ?? false) {
            $msg .= ' + ';
        } else {
            $msg = 'Warning: no Employees record matched this email. ';
        }
        $msg .= ($result['mailed'] ?? false) ? 'email sent' : 'email failed';
        $msg .= ' for '.$interview->candidate_name.'.';

        return back()->with('success', $msg);
    }

    public function interviewBulkNotifyHr(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:interviews,id'],
            'hr_email' => ['required', 'email'],
            'meeting_link' => ['nullable', 'string', 'max:500'],
        ]);

        $sent = 0;
        $assigned = 0;
        $failed = 0;
        foreach (Interview::query()->with('application')->whereIn('id', $data['ids'])->get() as $interview) {
            $result = InterviewHrNotifier::notify(
                $interview,
                $data['hr_email'] ?? null,
                $data['meeting_link'] ?? null,
            );
            if ($result['ok'] ?? false) {
                $sent++;
                if ($result['assigned'] ?? false) {
                    $assigned++;
                }
            } else {
                $failed++;
            }
        }

        $msg = $sent.' interview(s) processed ('.$assigned.' linked to HR panel).';
        if ($failed) {
            $msg .= ' '.$failed.' failed.';
        }

        return redirect()
            ->route('admin.module.index', 'interviews')
            ->with('success', $msg);
    }

    public function applicationStatus(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,shortlisted,interview,offered,hired,internship_offered,rejected'],
        ]);

        $app = JobApplication::query()->findOrFail($id);
        $app->update(['status' => $data['status']]);

        $mail = ApplicationNotifier::notify($app->fresh(), $data['status']);

        $labels = [
            'new' => 'marked as New',
            'reviewed' => 'marked as Reviewed',
            'shortlisted' => 'Shortlisted',
            'interview' => 'Interview scheduled',
            'offered' => 'Offered',
            'hired' => 'Approved / Hired',
            'internship_offered' => 'Job declined — Internship offered',
            'rejected' => 'Rejected',
        ];

        $msg = $app->full_name.' — '.($labels[$data['status']] ?? $data['status']);
        if ($mail['sent']) {
            $msg .= ' — email sent to '.$app->email.'.';
        } elseif ($mail['error']) {
            $msg .= ' — status saved, but email failed: '.$mail['error'];
        }

        return back()->with($mail['sent'] || ! $mail['error'] ? 'success' : 'success', $msg);
    }

    public function applicationBulkStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:job_applications,id'],
            'status' => ['required', 'in:reviewed,shortlisted,interview,offered,hired,internship_offered,rejected'],
        ]);

        $apps = JobApplication::query()->whereIn('id', $data['ids'])->get();
        $updated = 0;
        $mailed = 0;
        $failed = [];

        foreach ($apps as $app) {
            $app->update(['status' => $data['status']]);
            $mail = ApplicationNotifier::notify($app->fresh(), $data['status']);
            $updated++;
            if ($mail['sent']) {
                $mailed++;
            } elseif ($mail['error']) {
                $failed[] = $app->full_name.': '.$mail['error'];
            }
        }

        $msg = $updated.' application(s) updated to '.ucfirst($data['status']).'. Emails sent: '.$mailed.'.';
        if ($failed) {
            $msg .= ' Failures: '.implode(' | ', array_slice($failed, 0, 3));
        }

        return redirect()
            ->route('admin.module.index', 'applications')
            ->with('success', $msg);
    }

    public function update(Request $request, string $module, int $id): RedirectResponse|JsonResponse
    {
        $mod = $this->module($module);
        $item = $this->queryFor($mod)->findOrFail($id);
        $data = $this->validated($request, $mod, 'edit', $id);
        $panelPassword = trim((string) $request->input('panel_password', $data['panel_password'] ?? ''));
        unset($data['panel_password'], $data['department_name'], $data['photo_path']);

        if ($module === 'internships') {
            $data['employment_type'] = 'Internship';
            $data['is_open'] = $request->boolean('is_open');
        }
        if ($module === 'jobs') {
            $data['is_open'] = $request->boolean('is_open');
        }
        if ($module === 'employees') {
            $data['department_id'] = $this->departmentIdFromName((string) $request->input('department_name', $item->department?->name ?? ''));
            $this->storeEmployeePhoto($request, $item);
        }

        $item->update($data);

        $extra = '';
        if ($module === 'employees') {
            $sendMail = $panelPassword !== '';
            $result = EmployeeAccountService::provision(
                $item->fresh()->load('department'),
                $sendMail ? $panelPassword : null,
                $sendMail,
                true,
            );
            if (! empty($result['error']) && $result['error'] === 'email_is_admin') {
                $extra = ' Warning: email matches an admin account — panel not linked.';
            } elseif ($sendMail && ($result['mailed'] ?? false)) {
                $extra = ' Password updated and welcome email resent.';
            } elseif ($sendMail) {
                $extra = ' Password updated (email could not be sent).';
            } elseif ($result['user'] ?? null) {
                $extra = ' Employee panel login synced.';
            }
        }

        return Ajax::ok($request, $mod['title'].' updated successfully.'.$extra, route('admin.module.index', $module));
    }

    public function destroy(string $module, int $id): RedirectResponse
    {
        $mod = $this->module($module);
        $item = $this->queryFor($mod)->findOrFail($id);

        if ($module === 'employees') {
            \App\Models\User::query()
                ->where('employee_id', $item->id)
                ->where('role', \App\Models\User::ROLE_EMPLOYEE)
                ->delete();
        }

        $item->delete();

        return redirect()
            ->route('admin.module.index', $module)
            ->with('success', $mod['title'].' deleted.');
    }

    protected function validated(Request $request, array $mod, string $mode = 'create', ?int $ignoreId = null): array
    {
        $rules = [];
        foreach ($mod['fields'] as $field) {
            if (! empty($field['show_only'])) {
                continue;
            }
            $name = $field['name'];
            $rule = [];
            $required = ! empty($field['required']);
            if (! empty($field['required_on_create'])) {
                $required = $mode === 'create';
            }
            $rule[] = $required ? 'required' : 'nullable';
            $type = $field['type'] ?? 'text';
            if ($type === 'email') {
                $rule[] = 'email';
                if (($mod['model'] ?? null) === Employee::class && $name === 'email') {
                    $unique = Rule::unique('employees', 'email');
                    if ($mode === 'edit' && $ignoreId) {
                        $unique = $unique->ignore($ignoreId);
                    }
                    $rule[] = $unique;
                }
            }
            if ($type === 'password' || $name === 'panel_password') {
                // Edit: blank allowed; Create: required via required_on_create
                if ($mode === 'edit') {
                    $rule = ['nullable', 'string', 'min:6'];
                } else {
                    $rule[] = 'string';
                    $rule[] = 'min:6';
                }
            }
            if ($type === 'file') {
                $rule = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'];
            }
            if ($type === 'number' || $type === 'model_select') {
                $rule[] = 'numeric';
            }
            if ($type === 'checkbox') {
                $rule = ['sometimes', 'boolean'];
            }
            if ($type === 'select' && ! empty($field['options'])) {
                $rule[] = 'in:'.implode(',', $field['options']);
            }
            $rules[$name] = $rule;
        }

        $data = $request->validate($rules);

        foreach ($mod['fields'] as $field) {
            if (! empty($field['show_only'])) {
                continue;
            }
            if (($field['type'] ?? '') === 'checkbox') {
                $data[$field['name']] = $request->boolean($field['name']);
            }
        }

        return $data;
    }

    protected function departmentIdFromName(string $name): ?int
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        $dept = Department::query()->firstOrCreate(
            ['name' => $name],
            ['code' => strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name) ?: 'DEP', 0, 8)), 'is_active' => true]
        );

        return $dept->id;
    }

    protected function storeEmployeePhoto(Request $request, Employee $employee): void
    {
        if (! $request->hasFile('photo_path')) {
            return;
        }

        if ($employee->photo_path) {
            Storage::disk('public')->delete($employee->photo_path);
        }

        $path = $request->file('photo_path')->store('employees', 'public');
        $employee->update(['photo_path' => $path]);
    }

    protected function showEmployee(Employee $employee): View
    {
        $employee->load(['department', 'user']);
        $attendance = $employee->attendance()->latest('date')->limit(365)->get();
        $payroll = $employee->payroll()->latest('month')->limit(12)->get()->sortBy('month')->values();

        $monthLabels = [];
        $presentCounts = [];
        $lateCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->timezone('Asia/Kolkata')->startOfMonth()->subMonths($i);
            $end = $start->copy()->endOfMonth();
            $monthLabels[] = $start->format('M Y');
            $presentCounts[] = $attendance->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->whereIn('status', ['present', 'late'])->count();
            $lateCounts[] = $attendance->whereBetween('date', [$start->toDateString(), $end->toDateString()])
                ->where('status', 'late')->count();
        }

        $payLabels = $payroll->map(fn ($p) => method_exists($p, 'monthLabel') ? $p->monthLabel() : $p->month)->values();
        $payValues = $payroll->map(fn ($p) => (float) $p->net_pay)->values();

        $assignments = WorkTaskAssignee::query()
            ->with('task')
            ->where('employee_id', $employee->id)
            ->get();
        $delayed = 0;
        $onTime = 0;
        $now = now();
        foreach ($assignments as $row) {
            $due = $row->task?->due_at;
            $done = $row->status === 'completed';
            if ($due && (($done && $row->updated_at && $row->updated_at->gt($due)) || (! $done && $now->gt($due)))) {
                $delayed++;
            } else {
                $onTime++;
            }
        }

        return view('admin.employees.show', [
            'user' => Auth::user(),
            'mod' => $this->module('employees'),
            'item' => $employee,
            'attendance' => $attendance->take(30),
            'payroll' => $payroll,
            'monthLabels' => $monthLabels,
            'presentCounts' => $presentCounts,
            'lateCounts' => $lateCounts,
            'payLabels' => $payLabels,
            'payValues' => $payValues,
            'delayedTasks' => $delayed,
            'onTimeTasks' => $onTime,
            'totalTasks' => $assignments->count(),
        ]);
    }
}
