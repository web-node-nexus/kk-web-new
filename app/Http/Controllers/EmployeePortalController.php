<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\AttendanceRecord;
use App\Models\EmployeeNotification;
use App\Models\Interview;
use App\Models\LeaveRequest;
use App\Models\LiveChatMessage;
use App\Models\PayrollRecord;
use App\Models\WorkTask;
use App\Models\WorkTaskAssignee;
use App\Models\WorkTaskReply;
use App\Support\AppTime;
use App\Support\Ajax;
use App\Support\TaskMedia;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class EmployeePortalController extends Controller
{
    protected function employeeOrAbort()
    {
        $employee = Auth::user()?->employee?->load('department');
        if (! $employee) {
            abort(403, 'Employee profile not linked. Contact HR.');
        }

        return $employee;
    }

    protected function requireFeature(string $key): void
    {
        abort_unless(
            \App\Support\SiteSettings::bool($key),
            403,
            'This feature is currently disabled by admin.'
        );
    }

    public function dashboard(): View
    {
        $user = Auth::user();
        $employee = $user->employee?->load('department');
        $tz = AppTime::tz();
        $today = Carbon::now($tz)->toDateString();
        $monthStart = Carbon::now($tz)->startOfMonth()->toDateString();

        $attendanceQuery = $employee
            ? AttendanceRecord::query()->where('employee_id', $employee->id)
            : null;

        $presentThisMonth = $attendanceQuery
            ? (clone $attendanceQuery)->whereBetween('date', [$monthStart, $today])->where('status', 'present')->count()
            : 0;
        $pendingLeaves = $employee
            ? LeaveRequest::query()->where('employee_id', $employee->id)->where('status', 'pending')->count()
            : 0;
        $todayRow = $employee
            ? AttendanceRecord::query()->where('employee_id', $employee->id)->whereDate('date', $today)->first()
            : null;
        $latestPay = $employee
            ? PayrollRecord::query()->where('employee_id', $employee->id)->latest('id')->first()
            : null;

        $assignedInterviews = $employee
            ? Interview::query()
                ->with('application')
                ->where(function ($q) use ($employee) {
                    $q->where('assigned_hr_employee_id', $employee->id)
                        ->orWhereRaw('LOWER(hr_email) = ?', [strtolower((string) $employee->email)]);
                })
                ->whereIn('status', ['scheduled', 'approved'])
                ->latest('scheduled_at')
                ->take(5)
                ->get()
            : collect();
        $assignedInterviewCount = $employee
            ? Interview::query()
                ->where(function ($q) use ($employee) {
                    $q->where('assigned_hr_employee_id', $employee->id)
                        ->orWhereRaw('LOWER(hr_email) = ?', [strtolower((string) $employee->email)]);
                })
                ->whereIn('status', ['scheduled', 'approved'])
                ->count()
            : 0;

        $openTaskCount = $employee
            ? WorkTaskAssignee::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['pending', 'in_progress'])
                ->count()
            : 0;

        $attMonthLabels = [];
        $attPresentCounts = [];
        $attLateCounts = [];
        if ($employee) {
            $yearRows = AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->where('date', '>=', Carbon::now($tz)->subMonths(5)->startOfMonth()->toDateString())
                ->get();
            for ($i = 5; $i >= 0; $i--) {
                $start = Carbon::now($tz)->startOfMonth()->subMonths($i);
                $end = $start->copy()->endOfMonth();
                $attMonthLabels[] = $start->format('M');
                $slice = $yearRows->filter(function ($row) use ($start, $end) {
                    $d = optional($row->date)->toDateString();

                    return $d && $d >= $start->toDateString() && $d <= $end->toDateString();
                });
                $attPresentCounts[] = $slice->whereIn('status', ['present', 'late'])->count();
                $attLateCounts[] = $slice->where('status', 'late')->count();
            }
        }

        return view('employee.dashboard', [
            'user' => $user,
            'employee' => $employee,
            'presentThisMonth' => $presentThisMonth,
            'pendingLeaves' => $pendingLeaves,
            'todayRow' => $todayRow,
            'latestPay' => $latestPay,
            'assignedInterviews' => $assignedInterviews,
            'assignedInterviewCount' => $assignedInterviewCount,
            'attendance' => $employee
                ? AttendanceRecord::query()->where('employee_id', $employee->id)->latest('date')->take(6)->get()
                : collect(),
            'allAttendance' => $employee
                ? AttendanceRecord::query()->where('employee_id', $employee->id)->latest('date')->paginate(15)
                : new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15),
            'attMonthLabels' => $attMonthLabels,
            'attPresentCounts' => $attPresentCounts,
            'attLateCounts' => $attLateCounts,
            'leaves' => $employee
                ? LeaveRequest::query()->where('employee_id', $employee->id)->latest()->take(5)->get()
                : collect(),
            'payroll' => $employee
                ? PayrollRecord::query()->where('employee_id', $employee->id)->latest()->take(4)->get()
                : collect(),
            'openTaskCount' => $openTaskCount,
        ]);
    }

    public function attendance(): View
    {
        $employee = $this->employeeOrAbort();
        $tz = AppTime::tz();
        $today = Carbon::now($tz)->toDateString();
        $todayRow = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        return view('employee.attendance', [
            'user' => Auth::user(),
            'employee' => $employee,
            'todayRow' => $todayRow,
            'records' => AttendanceRecord::query()
                ->where('employee_id', $employee->id)
                ->latest('date')
                ->paginate(50),
        ]);
    }

    public function checkIn(): RedirectResponse|JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $tz = AppTime::tz();
        $now = Carbon::now($tz);
        $today = $now->toDateString();
        $time = $now->format('H:i:s');
        $lateAfter = \App\Support\SiteSettings::get('late_checkin_after', '10:30') ?: '10:30';
        $status = $now->format('H:i') > $lateAfter ? 'late' : 'present';

        $row = AttendanceRecord::query()->firstOrCreate(
            ['employee_id' => $employee->id, 'date' => $today],
            ['check_in' => $time, 'status' => $status]
        );

        if (! $row->check_in) {
            $row->update(['check_in' => $time, 'status' => $status]);
        }

        $saved = $row->fresh();
        $label = $saved->status === 'late' ? 'Checked in (late)' : 'Checked in';
        $shown = $saved->check_in ? Carbon::parse($saved->check_in)->format('h:i A') : $now->format('h:i A');

        return Ajax::ok(request(), $label.' at '.$shown.' (IST). Admin Attendance board pe save ho gaya.');
    }

    public function checkOut(): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $tz = AppTime::tz();
        $now = Carbon::now($tz);
        $today = $now->toDateString();
        $time = $now->format('H:i:s');

        $row = AttendanceRecord::query()
            ->where('employee_id', $employee->id)
            ->whereDate('date', $today)
            ->first();

        if (! $row || ! $row->check_in) {
            return Ajax::fail(request(), 'Please check in first.', ['attendance' => ['Please check in first.']]);
        }

        if ($row->check_out) {
            return Ajax::ok(request(), 'Already checked out today at '.Carbon::parse($row->check_out)->format('h:i A').'.');
        }

        $row->update(['check_out' => $time]);

        return Ajax::ok(request(), 'Checked out at '.$now->format('h:i A').' (IST).');
    }

    public function leaves(): View
    {
        $this->requireFeature('feature_employee_leaves');
        $employee = $this->employeeOrAbort();

        return view('employee.leaves', [
            'user' => Auth::user(),
            'employee' => $employee,
            'leaves' => LeaveRequest::query()
                ->where('employee_id', $employee->id)
                ->latest()
                ->paginate(15),
        ]);
    }

    public function storeLeave(Request $request): RedirectResponse|JsonResponse
    {
        $this->requireFeature('feature_employee_leaves');
        $employee = $this->employeeOrAbort();
        $data = $request->validate([
            'leave_type' => ['required', 'string', 'max:80'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $start = Carbon::parse($data['start_date']);
        $end = Carbon::parse($data['end_date']);
        $days = $start->diffInDays($end) + 1;

        LeaveRequest::query()->create([
            'employee_id' => $employee->id,
            'leave_type' => $data['leave_type'],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'days' => $days,
            'reason' => $data['reason'],
            'status' => 'pending',
        ]);

        return Ajax::ok($request, 'Leave request submitted. Status: Pending — admin approve karega tab hi leave approved hogi. Email confirmation approval ke baad aayegi.');
    }

    public function announcements(): View
    {
        $this->requireFeature('feature_announcements');
        $employee = $this->employeeOrAbort();

        return view('employee.announcements', [
            'user' => Auth::user(),
            'employee' => $employee,
            'announcements' => Announcement::query()
                ->forEmployee($employee->id)
                ->latest('published_at')
                ->latest('id')
                ->paginate(15),
        ]);
    }

    public function payroll(): View
    {
        $employee = $this->employeeOrAbort();

        return view('employee.payroll', [
            'user' => Auth::user(),
            'employee' => $employee,
            'payroll' => PayrollRecord::query()
                ->where('employee_id', $employee->id)
                ->orderByDesc('month')
                ->orderByDesc('id')
                ->paginate(12),
        ]);
    }

    public function interviews(): View
    {
        $this->requireFeature('feature_employee_interviews');
        $employee = $this->employeeOrAbort();
        $email = strtolower((string) $employee->email);

        $interviews = Interview::query()
            ->with('application')
            ->where(function ($q) use ($employee, $email) {
                $q->where('assigned_hr_employee_id', $employee->id)
                    ->orWhereRaw('LOWER(hr_email) = ?', [$email]);
            })
            ->latest('scheduled_at')
            ->paginate(15);

        return view('employee.interviews', [
            'user' => Auth::user(),
            'employee' => $employee,
            'interviews' => $interviews,
        ]);
    }

    public function interviewShow(int $id): View
    {
        $employee = $this->employeeOrAbort();
        $email = strtolower((string) $employee->email);

        $interview = Interview::query()
            ->with('application')
            ->where('id', $id)
            ->where(function ($q) use ($employee, $email) {
                $q->where('assigned_hr_employee_id', $employee->id)
                    ->orWhereRaw('LOWER(hr_email) = ?', [$email]);
            })
            ->firstOrFail();

        return view('employee.interview-show', [
            'user' => Auth::user(),
            'employee' => $employee,
            'interview' => $interview,
            'application' => $interview->application,
        ]);
    }

    public function interviewComplete(int $id): RedirectResponse
    {
        $employee = $this->employeeOrAbort();
        $email = strtolower((string) $employee->email);

        $interview = Interview::query()
            ->where('id', $id)
            ->where(function ($q) use ($employee, $email) {
                $q->where('assigned_hr_employee_id', $employee->id)
                    ->orWhereRaw('LOWER(hr_email) = ?', [$email]);
            })
            ->firstOrFail();

        $interview->update(['status' => 'completed']);

        return back()->with('success', 'Interview marked as completed.');
    }

    public function profile(): View
    {
        return view('employee.profile', [
            'user' => Auth::user(),
            'employee' => Auth::user()->employee?->load('department'),
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = Auth::user();
        if (! Hash::check($data['current_password'], $user->password)) {
            return Ajax::fail($request, 'Current password is incorrect.', ['current_password' => ['Current password is incorrect.']]);
        }

        $user->update(['password' => $data['password']]);

        return Ajax::ok($request, 'Password updated successfully.');
    }

    public function tasks(): View
    {
        $employee = $this->employeeOrAbort();
        $assignments = WorkTaskAssignee::query()
            ->with(['task.creator'])
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(20);

        return view('employee.tasks.index', compact('assignments'));
    }

    public function showTask(WorkTask $task): View
    {
        $employee = $this->employeeOrAbort();
        $assignment = WorkTaskAssignee::query()
            ->where('work_task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        if (! $assignment->seen_at) {
            $assignment->update(['seen_at' => now()]);
        }

        $task->load(['creator', 'replies.employee', 'replies.user']);

        EmployeeNotification::query()
            ->where('employee_id', $employee->id)
            ->where('url', '/employee/tasks/'.$task->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('employee.tasks.show', compact('task', 'assignment'));
    }

    public function replyToTask(Request $request, WorkTask $task): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $employee = $this->employeeOrAbort();
        WorkTaskAssignee::query()
            ->where('work_task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $data = $request->validate(array_merge([
            'message' => ['nullable', 'string', 'max:4000'],
        ], TaskMedia::rules()));

        $media = TaskMedia::store($request, 'task-media/replies');
        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '' && ! TaskMedia::hasAny((object) $media)) {
            return Ajax::fail($request, 'Write a reply or attach a file / link.', ['message' => ['Write a reply or attach a file / link.']]);
        }

        WorkTaskReply::query()->create(array_merge([
            'work_task_id' => $task->id,
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'sender_type' => 'employee',
            'message' => $message,
        ], $media));

        $reply = WorkTaskReply::query()->latest('id')->first();

        return Ajax::ok($request, 'Reply sent.', extra: [
            'reply' => [
                'id' => $reply?->id,
                'sender' => $employee->name ?: Auth::user()->name,
                'sender_type' => 'employee',
                'message' => $message,
                'time' => $reply?->created_at ? \App\Support\AppTime::formatShort($reply->created_at) : '',
            ],
        ]);
    }

    public function updateTaskStatus(Request $request, WorkTask $task): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $assignment = WorkTaskAssignee::query()
            ->where('work_task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $data = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $assignment->update(['status' => $data['status']]);
        $task->refreshStatusFromAssignees();

        return Ajax::ok($request, 'Task status updated.');
    }

    public function taskReplies(Request $request, WorkTask $task): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        WorkTaskAssignee::query()
            ->where('work_task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();

        $after = $request->integer('after');
        $rows = WorkTaskReply::query()
            ->with(['employee', 'user'])
            ->where('work_task_id', $task->id)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderBy('id')
            ->get();

        return response()->json([
            'replies' => $rows->map(fn ($r) => [
                'id' => $r->id,
                'sender' => $r->senderName(),
                'sender_type' => $r->sender_type,
                'message' => $r->message,
                'time' => $r->created_at ? \App\Support\AppTime::formatShort($r->created_at) : '',
            ]),
        ]);
    }

    public function taskFeed(Request $request): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $after = $request->integer('after');
        $rows = WorkTaskAssignee::query()
            ->with('task')
            ->where('employee_id', $employee->id)
            ->when($after > 0, fn ($q) => $q->where('id', '>', $after))
            ->orderByDesc('id')
            ->limit(20)
            ->get();

        return response()->json([
            'items' => $rows->map(function ($row) {
                $task = $row->task;

                return [
                    'id' => $row->id,
                    'status' => $row->status,
                    'title' => $task?->title ?: 'Task',
                    'priority' => ucfirst($task?->priority ?: 'normal'),
                    'sent' => $task ? \App\Support\AppTime::formatShort($task->created_at) : '—',
                    'due' => $task?->dueLabel() ?: '—',
                    'excerpt' => \Illuminate\Support\Str::limit(strip_tags((string) ($task?->description ?? '')), 70),
                    'url' => $task ? route('employee.tasks.show', $task) : '#',
                ];
            })->values(),
            'latest_id' => (int) WorkTaskAssignee::query()->where('employee_id', $employee->id)->max('id'),
        ]);
    }

    public function chat(): View
    {
        $this->employeeOrAbort();

        return view('employee.chat.index');
    }

    public function notifications(): View
    {
        abort_unless(Auth::user()?->hasAnyEmployeeModule(), 403, 'Admin ne ye page aapke role me enable nahi kiya.');
        $employee = $this->employeeOrAbort();
        $notifications = EmployeeNotification::query()
            ->where('employee_id', $employee->id)
            ->latest()
            ->paginate(30);

        return view('employee.notifications', compact('notifications'));
    }

    public function openNotification(int $id): RedirectResponse
    {
        $employee = $this->employeeOrAbort();
        $note = EmployeeNotification::query()
            ->where('employee_id', $employee->id)
            ->findOrFail($id);

        if (! $note->read_at) {
            $note->update(['read_at' => now()]);
        }

        $url = $note->url ?: route('employee.dashboard');

        return redirect($url);
    }

    public function markNotificationsRead(): RedirectResponse
    {
        $employee = $this->employeeOrAbort();
        EmployeeNotification::query()
            ->where('employee_id', $employee->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'All notifications marked as read.');
    }

    public function chatMessages(Request $request): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $after = $request->integer('after');

        $query = LiveChatMessage::query()
            ->where('employee_id', $employee->id)
            ->with(['employee', 'user'])
            ->orderBy('id');

        if ($after > 0) {
            $query->where('id', '>', $after);
        }

        $messages = $query->limit(200)->get()->map(fn (LiveChatMessage $m) => [
            'id' => $m->id,
            'sender_type' => $m->sender_type,
            'sender' => $m->senderName(),
            'message' => $m->message,
            'time' => $m->created_at ? \App\Support\AppTime::formatShort($m->created_at) : '',
        ]);

        LiveChatMessage::query()
            ->where('employee_id', $employee->id)
            ->where('sender_type', 'admin')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['messages' => $messages]);
    }

    public function sendChat(Request $request): JsonResponse
    {
        $employee = $this->employeeOrAbort();
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $msg = LiveChatMessage::query()->create([
            'employee_id' => $employee->id,
            'user_id' => Auth::id(),
            'sender_type' => 'employee',
            'message' => $data['message'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $msg->id,
                'sender_type' => 'employee',
                'sender' => $employee->name ?: Auth::user()->name,
                'message' => $msg->message,
                'time' => $msg->created_at ? \App\Support\AppTime::formatShort($msg->created_at) : '',
            ],
        ]);
    }

    public function chatStream(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $employee = $this->employeeOrAbort();
        $after = $request->integer('after');

        return response()->stream(function () use ($employee, $after) {
            $last = $after;
            ignore_user_abort(true);
            for ($i = 0; $i < 25; $i++) {
                $query = LiveChatMessage::query()
                    ->where('employee_id', $employee->id)
                    ->with(['employee', 'user'])
                    ->orderBy('id');
                if ($last > 0) {
                    $query->where('id', '>', $last);
                }
                $rows = $query->limit(100)->get();
                if ($rows->isNotEmpty()) {
                    $last = (int) $rows->max('id');
                    echo 'data: '.json_encode([
                        'messages' => $rows->map(fn (LiveChatMessage $m) => [
                            'id' => $m->id,
                            'sender_type' => $m->sender_type,
                            'sender' => $m->senderName(),
                            'message' => $m->message,
                            'time' => $m->created_at ? \App\Support\AppTime::formatShort($m->created_at) : '',
                        ]),
                    ])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                    LiveChatMessage::query()
                        ->where('employee_id', $employee->id)
                        ->where('sender_type', 'admin')
                        ->whereNull('read_at')
                        ->update(['read_at' => now()]);
                } else {
                    echo "event: ping\ndata: {}\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }
                if (connection_aborted()) {
                    break;
                }
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    public function taskStream(Request $request, WorkTask $task): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $employee = $this->employeeOrAbort();
        WorkTaskAssignee::query()
            ->where('work_task_id', $task->id)
            ->where('employee_id', $employee->id)
            ->firstOrFail();
        $after = $request->integer('after');

        return response()->stream(function () use ($task, $after) {
            $last = $after;
            ignore_user_abort(true);
            for ($i = 0; $i < 25; $i++) {
                $rows = WorkTaskReply::query()
                    ->with(['employee', 'user'])
                    ->where('work_task_id', $task->id)
                    ->when($last > 0, fn ($q) => $q->where('id', '>', $last))
                    ->orderBy('id')
                    ->get();
                if ($rows->isNotEmpty()) {
                    $last = (int) $rows->max('id');
                    echo 'data: '.json_encode([
                        'replies' => $rows->map(fn ($r) => [
                            'id' => $r->id,
                            'sender' => $r->senderName(),
                            'sender_type' => $r->sender_type,
                            'message' => $r->message,
                            'time' => $r->created_at ? \App\Support\AppTime::formatShort($r->created_at) : '',
                        ]),
                    ])."\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                } else {
                    echo "event: ping\ndata: {}\n\n";
                    if (ob_get_level()) {
                        ob_flush();
                    }
                    flush();
                }
                if (connection_aborted()) {
                    break;
                }
                sleep(2);
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
        ]);
    }
}
