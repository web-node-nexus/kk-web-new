<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminInbox;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InboxController extends Controller
{
    public function notifications(): View
    {
        $user = Auth::user();
        $inbox = AdminInbox::snapshot($user, 80);

        return view('admin.inbox.notifications', [
            'user' => $user,
            'items' => $inbox['notifications'],
        ]);
    }

    public function messages(): View
    {
        $user = Auth::user();
        $inbox = AdminInbox::snapshot($user, 80);

        return view('admin.inbox.messages', [
            'user' => $user,
            'items' => $inbox['messages'],
        ]);
    }

    public function summary(): JsonResponse
    {
        $inbox = AdminInbox::snapshot(Auth::user(), 1);

        return response()->json([
            'notifications' => (int) $inbox['notification_count'],
            'messages' => (int) $inbox['message_count'],
        ]);
    }
}
