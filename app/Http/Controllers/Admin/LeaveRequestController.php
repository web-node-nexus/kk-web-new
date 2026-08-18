<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\LeaveStatusMail;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class LeaveRequestController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->has('status')
            ? ($request->string('status')->toString() ?: 'all')
            : 'pending';
        $q = trim((string) $request->string('q'));

        $base = LeaveRequest::query()->with('employee');

        $counts = [
            'all' => (clone $base)->count(),
            'pending' => (clone $base)->where('status', 'pending')->count(),
            'approved' => (clone $base)->where('status', 'approved')->count(),
            'rejected' => (clone $base)->where('status', 'rejected')->count(),
        ];

        $query = LeaveRequest::query()
            ->with('employee')
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->where(function ($inner) use ($like) {
                    $inner->where('leave_type', 'like', $like)
                        ->orWhere('reason', 'like', $like)
                        ->orWhereHas('employee', function ($e) use ($like) {
                            $e->where('name', 'like', $like)
                                ->orWhere('email', 'like', $like)
                                ->orWhere('employee_code', 'like', $like);
                        });
                });
            })
            ->latest();

        return view('admin.leaves.index', [
            'user' => Auth::user(),
            'items' => $query->paginate(20)->withQueryString(),
            'filters' => ['status' => $status, 'q' => $q],
            'counts' => $counts,
        ]);
    }

    public function decide(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'admin_note' => ['nullable', 'string', 'max:500'],
        ]);

        $leave = LeaveRequest::query()->with('employee')->findOrFail($id);

        if ($leave->status !== 'pending') {
            return back()->withErrors(['leave' => 'This leave is already '.$leave->status.'.']);
        }

        $leave->update(['status' => $data['status']]);

        $mailed = $this->notifyEmployee($leave->fresh('employee'), $data['status'], $data['admin_note'] ?? null);

        $msg = 'Leave '.$data['status'].' for '.($leave->employee?->name ?? 'employee').'.';
        $msg .= $mailed ? ' Email sent to employee.' : ' Email could not be sent (check mail settings).';

        return back()->with('success', $msg);
    }

    protected function notifyEmployee(LeaveRequest $leave, string $status, ?string $note): bool
    {
        if (! \App\Support\SiteSettings::bool('mail_leave_status')) {
            return false;
        }

        $email = $leave->employee?->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            Mail::to($email)->send(new LeaveStatusMail($leave, $status, $note));

            return true;
        } catch (Throwable $e) {
            Log::warning('Leave status mail failed', [
                'leave_id' => $leave->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
