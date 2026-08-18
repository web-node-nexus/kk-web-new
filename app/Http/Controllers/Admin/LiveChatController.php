<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\LiveChatMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LiveChatController extends Controller
{
    public function index(Request $request): View
    {
        $employeeId = $request->integer('employee');
        $employees = Employee::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'role_title']);

        $active = $employeeId
            ? $employees->firstWhere('id', $employeeId) ?? Employee::query()->find($employeeId)
            : $employees->first();

        $unread = LiveChatMessage::query()
            ->selectRaw('employee_id, COUNT(*) as total')
            ->where('sender_type', 'employee')
            ->whereNull('read_at')
            ->groupBy('employee_id')
            ->pluck('total', 'employee_id');

        $lastMessages = LiveChatMessage::query()
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('live_chat_messages')->groupBy('employee_id');
            })
            ->get()
            ->keyBy('employee_id');

        return view('admin.chat.index', [
            'user' => Auth::user(),
            'employees' => $employees,
            'active' => $active,
            'unread' => $unread,
            'lastMessages' => $lastMessages,
        ]);
    }

    public function messages(Request $request, int $employeeId): JsonResponse
    {
        $after = $request->integer('after');

        $query = LiveChatMessage::query()
            ->with(['employee', 'user'])
            ->where('employee_id', $employeeId)
            ->orderBy('id');

        if ($after > 0) {
            $query->where('id', '>', $after);
        }

        $rows = $query->take(200)->get();

        LiveChatMessage::query()
            ->where('employee_id', $employeeId)
            ->where('sender_type', 'employee')
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'messages' => $rows->map(fn (LiveChatMessage $m) => [
                'id' => $m->id,
                'sender_type' => $m->sender_type,
                'sender' => $m->senderName(),
                'message' => $m->message,
                'time' => $m->created_at ? \App\Support\AppTime::formatShort($m->created_at) : '',
            ]),
        ]);
    }

    public function stream(Request $request, int $employeeId): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        Employee::query()->findOrFail($employeeId);
        $after = $request->integer('after');

        return response()->stream(function () use ($employeeId, $after) {
            $last = $after;
            ignore_user_abort(true);
            for ($i = 0; $i < 25; $i++) {
                $query = LiveChatMessage::query()
                    ->with(['employee', 'user'])
                    ->where('employee_id', $employeeId)
                    ->orderBy('id');
                if ($last > 0) {
                    $query->where('id', '>', $last);
                }
                $rows = $query->take(100)->get();
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
                        ->where('employee_id', $employeeId)
                        ->where('sender_type', 'employee')
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

    public function send(Request $request, int $employeeId): JsonResponse
    {
        Employee::query()->findOrFail($employeeId);
        $data = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $msg = LiveChatMessage::query()->create([
            'employee_id' => $employeeId,
            'user_id' => Auth::id(),
            'sender_type' => 'admin',
            'message' => $data['message'],
        ]);

        return response()->json([
            'ok' => true,
            'message' => [
                'id' => $msg->id,
                'sender_type' => 'admin',
                'sender' => Auth::user()?->name ?: 'Admin',
                'message' => $msg->message,
                'time' => $msg->created_at ? \App\Support\AppTime::formatShort($msg->created_at) : '',
            ],
        ]);
    }
}
