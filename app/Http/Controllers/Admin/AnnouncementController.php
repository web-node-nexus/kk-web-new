<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->has('status')
            ? ($request->string('status')->toString() ?: 'all')
            : 'all';
        $q = trim((string) $request->string('q'));

        $base = Announcement::query();
        $counts = [
            'all' => (clone $base)->count(),
            'published' => (clone $base)->where('status', 'published')->count(),
            'draft' => (clone $base)->where('status', 'draft')->count(),
        ];

        $items = Announcement::query()
            ->with('employee')
            ->when($status !== 'all', fn ($qr) => $qr->where('status', $status))
            ->when($q !== '', function ($qr) use ($q) {
                $like = '%'.$q.'%';
                $qr->where(function ($inner) use ($like) {
                    $inner->where('title', 'like', $like)
                        ->orWhere('body', 'like', $like)
                        ->orWhere('audience', 'like', $like)
                        ->orWhereHas('employee', fn ($e) => $e->where('name', 'like', $like));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.announcements.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('status', 'q'),
            'counts' => $counts,
        ]);
    }

    public function create(): View
    {
        return view('admin.announcements.form', [
            'user' => Auth::user(),
            'item' => null,
            'mode' => 'create',
            'employees' => Employee::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        Announcement::query()->create($data);

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement saved. Published ones show in employee panel.');
    }

    public function edit(int $id): View
    {
        $item = Announcement::query()->with('employee')->findOrFail($id);

        return view('admin.announcements.form', [
            'user' => Auth::user(),
            'item' => $item,
            'mode' => 'edit',
            'employees' => Employee::query()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Announcement::query()->findOrFail($id);
        $item->update($this->validated($request));

        return redirect()
            ->route('admin.announcements.index')
            ->with('success', 'Announcement updated.');
    }

    public function destroy(int $id): RedirectResponse
    {
        Announcement::query()->findOrFail($id)->delete();

        return back()->with('success', 'Announcement deleted.');
    }

    public function publish(int $id): RedirectResponse
    {
        $item = Announcement::query()->findOrFail($id);
        $item->update([
            'status' => 'published',
            'published_at' => $item->published_at ?: now(),
        ]);

        return back()->with('success', 'Published — visible to selected employees now.');
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:10000'],
            'audience_type' => ['required', 'in:all,one'],
            'employee_id' => [
                Rule::requiredIf($request->input('audience_type') === 'one'),
                'nullable',
                'integer',
                'exists:employees,id',
            ],
            'status' => ['required', 'in:draft,published'],
        ]);

        if ($data['audience_type'] === 'all') {
            $data['employee_id'] = null;
            $data['audience'] = 'All employees';
        } else {
            $emp = Employee::query()->find($data['employee_id']);
            $data['audience'] = $emp ? 'Only: '.$emp->name : 'One employee';
        }

        if ($data['status'] === 'published') {
            $data['published_at'] = now();
        }

        return $data;
    }
}
