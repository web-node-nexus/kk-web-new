<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Employee;
use App\Support\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validated($request);
        $this->storeImage($request, $data);
        Announcement::query()->create($data);

        return Ajax::ok($request, 'Announcement saved. Published ones appear in the employee panel.', route('admin.announcements.index'));
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

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = Announcement::query()->findOrFail($id);
        $data = $this->validated($request);

        if ($request->boolean('remove_image') && $item->image_path) {
            Storage::disk('public')->delete($item->image_path);
            $data['image_path'] = null;
        }

        $this->storeImage($request, $data, $item);
        $item->update($data);

        return Ajax::ok($request, 'Announcement updated.', route('admin.announcements.index'));
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = Announcement::query()->findOrFail($id);
        if ($item->image_path) {
            Storage::disk('public')->delete($item->image_path);
        }
        $item->delete();

        return Ajax::ok($request, 'Announcement deleted.', route('admin.announcements.index'));
    }

    public function publish(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = Announcement::query()->findOrFail($id);
        $item->update([
            'status' => 'published',
            'published_at' => $item->published_at ?: now(),
        ]);

        return Ajax::ok($request, 'Published — visible to selected employees now.', route('admin.announcements.index'));
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'body' => ['required', 'string', 'max:10000'],
            'link_url' => ['nullable', 'url', 'max:500'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['nullable', 'boolean'],
            'audience_type' => ['required', 'in:all,one'],
            'employee_id' => [
                Rule::requiredIf($request->input('audience_type') === 'one'),
                'nullable',
                'integer',
                'exists:employees,id',
            ],
            'status' => ['required', 'in:draft,published'],
        ]);

        unset($data['image'], $data['remove_image']);

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

        $data['link_url'] = filled($data['link_url'] ?? null) ? $data['link_url'] : null;

        return $data;
    }

    /** @param array<string, mixed> $data */
    protected function storeImage(Request $request, array &$data, ?Announcement $existing = null): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        if ($existing?->image_path) {
            Storage::disk('public')->delete($existing->image_path);
        }

        $data['image_path'] = $request->file('image')->store('announcements', 'public');
    }
}
