<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContactInquiryController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->has('status')
            ? ($request->string('status')->toString() ?: 'all')
            : 'new';
        $q = trim((string) $request->string('q'));

        $base = ContactInquiry::query();
        $counts = [
            'all' => (clone $base)->count(),
            'new' => (clone $base)->where('status', 'new')->count(),
            'reviewed' => (clone $base)->where('status', 'reviewed')->count(),
            'closed' => (clone $base)->where('status', 'closed')->count(),
        ];

        $items = ContactInquiry::query()
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->where(function ($inner) use ($like) {
                    $inner->where('name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('phone', 'like', $like)
                        ->orWhere('company', 'like', $like)
                        ->orWhere('service', 'like', $like)
                        ->orWhere('message', 'like', $like);
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('status', 'q'),
            'counts' => $counts,
        ]);
    }

    public function show(int $id): View
    {
        $item = ContactInquiry::query()->findOrFail($id);

        // Opening a new inquiry marks it reviewed (still readable as "seen")
        if ($item->status === 'new') {
            $item->update(['status' => 'reviewed']);
            $item->refresh();
        }

        return view('admin.contacts.show', [
            'user' => Auth::user(),
            'item' => $item,
        ]);
    }

    public function status(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,closed'],
        ]);

        $item = ContactInquiry::query()->findOrFail($id);
        $item->update(['status' => $data['status']]);

        return back()->with('success', 'Inquiry marked as '.$data['status'].'.');
    }

    public function destroy(int $id): RedirectResponse
    {
        ContactInquiry::query()->findOrFail($id)->delete();

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Inquiry deleted.');
    }
}
