<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\WorkTask;
use App\Models\WorkTaskAssignee;
use App\Models\WorkTaskReply;
use App\Support\AppTime;
use App\Support\Ajax;
use App\Support\SafeHtml;
use App\Support\TaskMedia;
use App\Services\TaskNotifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class WorkTaskController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->string('status')->toString() ?: 'all';
        $q = trim((string) $request->string('q'));

        $base = WorkTask::query();
        $counts = [
            'all' => (clone $base)->count(),
            'open' => (clone $base)->where('status', 'open')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'done' => (clone $base)->where('status', 'done')->count(),
        ];

        $items = WorkTask::query()
            ->with(['assignees', 'creator'])
            ->withCount('assignees')
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->where(function ($inner) use ($like) {
                    $inner->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.tasks.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('status', 'q'),
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        return view('admin.tasks.form', [
            'user' => Auth::user(),
            'employees' => Employee::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(array_merge([
            'title' => ['required', 'string', 'max:190'],
            'description' => ['nullable', 'string', 'max:50000'],
            'priority' => ['required', 'in:low,normal,high'],
            'due_date' => ['nullable', 'date'],
            'due_time' => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'assign_mode' => ['required', 'in:all,selected'],
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ], TaskMedia::rules()));

        $assignAll = $data['assign_mode'] === 'all';
        $ids = $assignAll
            ? Employee::query()->where('status', 'active')->pluck('id')->all()
            : array_values(array_unique($data['employee_ids'] ?? []));

        if (! $assignAll && $ids === []) {
            return Ajax::fail($request, 'Select at least one employee, or choose All employees.', ['employee_ids' => ['Select at least one employee, or choose All employees.']]);
        }

        $dueAt = AppTime::fromDateAndTime($data['due_date'] ?? null, $data['due_time'] ?? null);

        $task = WorkTask::query()->create(array_merge([
            'title' => $data['title'],
            'description' => SafeHtml::clean($data['description'] ?? null),
            'priority' => $data['priority'],
            'due_date' => $data['due_date'] ?? null,
            'due_at' => $dueAt?->copy()->utc(),
            'status' => 'open',
            'assign_all' => $assignAll,
            'created_by' => Auth::id(),
        ], TaskMedia::store($request, 'task-media')));

        $employees = Employee::query()->whereIn('id', $ids)->get();
        foreach ($employees as $employee) {
            WorkTaskAssignee::query()->create([
                'work_task_id' => $task->id,
                'employee_id' => $employee->id,
                'status' => 'pending',
            ]);
        }

        $mailed = TaskNotifier::assigned($task, $employees);
        $msg = 'Task assigned to '.count($ids).' employee(s). It will show in their panel.';
        $msg .= $mailed > 0
            ? ' Email + notification sent to '.$mailed.' employee(s).'
            : ' Panel notification created. Email could not be sent (check mail settings).';

        return Ajax::ok($request, $msg, route('admin.tasks.show', $task->id));
    }

    public function show(int $id): View
    {
        $item = WorkTask::query()
            ->with(['assignees', 'assignmentRows.employee', 'replies.employee', 'replies.user', 'creator'])
            ->findOrFail($id);

        return view('admin.tasks.show', [
            'user' => Auth::user(),
            'item' => $item,
        ]);
    }

    public function reply(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = WorkTask::query()->findOrFail($id);
        $data = $request->validate(array_merge([
            'message' => ['nullable', 'string', 'max:4000'],
        ], TaskMedia::rules()));

        $media = TaskMedia::store($request, 'task-media/replies');
        $message = trim((string) ($data['message'] ?? ''));
        if ($message === '' && ! TaskMedia::hasAny((object) $media)) {
            return Ajax::fail($request, 'Write a reply or attach a file / link.', ['message' => ['Write a reply or attach a file / link.']]);
        }

        $reply = WorkTaskReply::query()->create(array_merge([
            'work_task_id' => $item->id,
            'user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $message,
        ], $media));

        $mailed = TaskNotifier::adminReply($item->fresh(), $message ?: 'Attachment / link sent');
        $msg = 'Reply sent to the task thread.';
        $msg .= $mailed > 0 ? ' Email + notification sent to employees.' : ' Panel notification created.';

        return Ajax::ok($request, $msg, extra: [
            'reply' => [
                'id' => $reply->id,
                'sender' => Auth::user()?->name,
                'sender_type' => 'admin',
                'message' => $message,
                'time' => \App\Support\AppTime::formatShort($reply->created_at),
            ],
        ]);
    }

    public function replies(Request $request, int $id): JsonResponse
    {
        $item = WorkTask::query()->findOrFail($id);
        $after = $request->integer('after');
        $rows = WorkTaskReply::query()
            ->with(['employee', 'user'])
            ->where('work_task_id', $item->id)
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

    public function stream(Request $request, int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $item = WorkTask::query()->findOrFail($id);
        $after = $request->integer('after');

        return response()->stream(function () use ($item, $after) {
            $last = $after;
            ignore_user_abort(true);
            for ($i = 0; $i < 25; $i++) {
                $rows = WorkTaskReply::query()
                    ->with(['employee', 'user'])
                    ->where('work_task_id', $item->id)
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
                    ]). "\n\n";
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

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $item = WorkTask::query()->findOrFail($id);
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,done'],
        ]);
        $item->update(['status' => $data['status']]);

        return back()->with('success', 'Task status updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        WorkTask::query()->findOrFail($id)->delete();

        return redirect()->route('admin.tasks.index')->with('success', 'Task deleted.');
    }
}
