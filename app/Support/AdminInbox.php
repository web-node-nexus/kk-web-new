<?php

namespace App\Support;

use App\Models\ContactInquiry;
use App\Models\JobApplication;
use App\Models\LeaveRequest;
use App\Models\LiveChatMessage;
use App\Models\ProjectRequest;
use App\Models\User;
use App\Models\WorkTaskReply;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class AdminInbox
{
    /**
     * Live admin bell + messages feed from existing records.
     *
     * @return array{
     *   notifications: Collection<int, array<string, mixed>>,
     *   messages: Collection<int, array<string, mixed>>,
     *   counts: array<string, int>
     * }
     */
    public static function snapshot(?User $user, int $limit = 10): array
    {
        $can = function (string $permission) use ($user): bool {
            return $user?->hasPermission($permission) ?? false;
        };

        $notifications = collect();
        $messages = collect();

        $counts = [
            'applications' => 0,
            'contacts' => 0,
            'projects' => 0,
            'leaves' => 0,
            'chats' => 0,
            'task_replies' => 0,
        ];

        try {
            if ($can('module.applications') && Schema::hasTable('job_applications')) {
                $counts['applications'] = JobApplication::query()->where('status', 'new')->count();
                JobApplication::query()
                    ->where('status', 'new')
                    ->latest('id')
                    ->limit($limit)
                    ->get()
                    ->each(function (JobApplication $app) use ($notifications) {
                        $notifications->push(self::item(
                            'New job application',
                            trim($app->full_name.' applied for '.($app->position ?: 'a role')),
                            route('admin.module.show', ['module' => 'applications', 'id' => $app->id]),
                            $app->created_at,
                            true
                        ));
                    });
            }
        } catch (Throwable) {
        }

        try {
            if ($can('module.projects') && Schema::hasTable('project_requests')) {
                $counts['projects'] = ProjectRequest::query()->where('status', 'new')->count();
                ProjectRequest::query()
                    ->where('status', 'new')
                    ->latest('id')
                    ->limit($limit)
                    ->get()
                    ->each(function (ProjectRequest $row) use ($notifications) {
                        $notifications->push(self::item(
                            'New project request',
                            trim($row->name.($row->service ? ' · '.$row->service : '')),
                            route('admin.module.show', ['module' => 'projects', 'id' => $row->id]),
                            $row->created_at,
                            true
                        ));
                    });
            }
        } catch (Throwable) {
        }

        try {
            if ($can('module.leaves') && Schema::hasTable('leave_requests')) {
                $counts['leaves'] = LeaveRequest::query()->where('status', 'pending')->count();
                LeaveRequest::query()
                    ->with('employee')
                    ->where('status', 'pending')
                    ->latest('id')
                    ->limit($limit)
                    ->get()
                    ->each(function (LeaveRequest $row) use ($notifications) {
                        $name = $row->employee?->name ?: 'Employee';
                        $notifications->push(self::item(
                            'Leave request pending',
                            $name.' · '.($row->leave_type ?: 'Leave').' · '.((int) $row->days).' day(s)',
                            route('admin.leaves.index', ['status' => 'pending']),
                            $row->created_at,
                            true
                        ));
                    });
            }
        } catch (Throwable) {
        }

        try {
            if ($can('module.tasks') && Schema::hasTable('work_task_replies')) {
                $counts['task_replies'] = WorkTaskReply::query()
                    ->where('sender_type', 'employee')
                    ->where('created_at', '>=', now()->subDays(2))
                    ->count();
                $replies = WorkTaskReply::query()
                    ->with(['employee', 'task'])
                    ->where('sender_type', 'employee')
                    ->where('created_at', '>=', now()->subDays(2))
                    ->latest('id')
                    ->limit($limit)
                    ->get();
                $replies->each(function (WorkTaskReply $row) use ($notifications) {
                    $name = $row->employee?->name ?: 'Employee';
                    $title = $row->task?->title ?: 'Task';
                    $notifications->push(self::item(
                        'Employee replied on task',
                        $name.' · '.$title,
                        route('admin.tasks.show', $row->work_task_id),
                        $row->created_at,
                        true
                    ));
                });
            }
        } catch (Throwable) {
        }

        try {
            if ($can('module.contacts') && Schema::hasTable('contact_inquiries')) {
                $counts['contacts'] = ContactInquiry::query()->where('status', 'new')->count();
                ContactInquiry::query()
                    ->where('status', 'new')
                    ->latest('id')
                    ->limit($limit)
                    ->get()
                    ->each(function (ContactInquiry $row) use ($messages) {
                        $messages->push(self::item(
                            'Website contact · '.$row->name,
                            Str::limit((string) $row->message, 80) ?: ($row->email ?: 'New inquiry'),
                            route('admin.contacts.show', $row->id),
                            $row->created_at,
                            true
                        ));
                    });
            }
        } catch (Throwable) {
        }

        try {
            if ($can('module.chat') && Schema::hasTable('live_chat_messages')) {
                $unread = LiveChatMessage::query()
                    ->selectRaw('employee_id, COUNT(*) as total, MAX(id) as last_id')
                    ->where('sender_type', 'employee')
                    ->whereNull('read_at')
                    ->groupBy('employee_id')
                    ->orderByDesc('last_id')
                    ->limit($limit)
                    ->get();
                $counts['chats'] = (int) LiveChatMessage::query()
                    ->where('sender_type', 'employee')
                    ->whereNull('read_at')
                    ->count();

                $lastIds = $unread->pluck('last_id')->filter()->all();
                $lastRows = $lastIds
                    ? LiveChatMessage::query()->with('employee')->whereIn('id', $lastIds)->get()->keyBy('id')
                    : collect();

                foreach ($unread as $row) {
                    $last = $lastRows->get($row->last_id);
                    $name = $last?->employee?->name ?: 'Employee';
                    $n = (int) $row->total;
                    $messages->push(self::item(
                        $name.' sent a chat',
                        ($n > 1 ? $n.' unread · ' : '').Str::limit((string) ($last?->message ?: 'New message'), 80),
                        route('admin.chat.index', ['employee' => $row->employee_id]),
                        $last?->created_at,
                        true
                    ));
                }
            }
        } catch (Throwable) {
        }

        $sort = fn (array $item) => optional($item['at'])->timestamp ?? 0;

        return [
            'notifications' => $notifications->sortByDesc($sort)->values()->take($limit),
            'messages' => $messages->sortByDesc($sort)->values()->take($limit),
            'counts' => $counts,
            'notification_count' => $counts['applications'] + $counts['projects'] + $counts['leaves'] + $counts['task_replies'],
            'message_count' => $counts['contacts'] + $counts['chats'],
        ];
    }

    /** @return array<string, mixed> */
    protected static function item(string $title, string $body, string $url, mixed $at, bool $unread): array
    {
        return [
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'at' => $at,
            'unread' => $unread,
        ];
    }
}
