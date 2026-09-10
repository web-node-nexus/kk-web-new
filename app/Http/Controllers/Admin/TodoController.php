<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PersonalTodo;
use App\Support\Ajax;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TodoController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->string('filter')->toString() ?: 'open';
        $q = trim((string) $request->string('q'));

        $base = PersonalTodo::query()->forAdmin(Auth::id());
        $counts = [
            'open' => (clone $base)->where('is_done', false)->count(),
            'done' => (clone $base)->where('is_done', true)->count(),
            'all' => (clone $base)->count(),
        ];

        $items = PersonalTodo::query()
            ->forAdmin(Auth::id())
            ->when($filter === 'open', fn ($qr) => $qr->where('is_done', false))
            ->when($filter === 'done', fn ($qr) => $qr->where('is_done', true))
            ->when($q !== '', fn ($qr) => $qr->where(function ($inner) use ($q) {
                $like = '%'.$q.'%';
                $inner->where('title', 'like', $like)->orWhere('notes', 'like', $like);
            }))
            ->orderBy('is_done')
            ->orderByRaw("FIELD(priority, 'high', 'medium', 'low')")
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        return view('admin.todos.index', [
            'user' => Auth::user(),
            'items' => $items,
            'filters' => compact('filter', 'q'),
            'counts' => $counts,
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $data = $this->validated($request);
        PersonalTodo::query()->create($data + [
            'owner_type' => 'admin',
            'owner_id' => Auth::id(),
            'is_done' => false,
        ]);

        return Ajax::ok($request, 'To-do added.', route('admin.todos.index'));
    }

    public function update(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = PersonalTodo::query()->forAdmin(Auth::id())->findOrFail($id);
        $item->update($this->validated($request));

        return Ajax::ok($request, 'To-do updated.', route('admin.todos.index'));
    }

    public function toggle(Request $request, int $id): RedirectResponse|JsonResponse
    {
        $item = PersonalTodo::query()->forAdmin(Auth::id())->findOrFail($id);
        if ($item->is_done) {
            $item->markOpen();
            $msg = 'Marked as open.';
        } else {
            $item->markDone();
            $msg = 'Marked as done.';
        }

        return Ajax::ok($request, $msg, route('admin.todos.index', request()->only('filter', 'q')));
    }

    public function destroy(Request $request, int $id): RedirectResponse|JsonResponse
    {
        PersonalTodo::query()->forAdmin(Auth::id())->whereKey($id)->delete();

        return Ajax::ok($request, 'To-do removed.', route('admin.todos.index'));
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:190'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'priority' => ['required', 'in:low,medium,high'],
            'due_date' => ['nullable', 'date'],
        ]);

        $data['notes'] = filled($data['notes'] ?? null) ? $data['notes'] : null;
        $data['due_date'] = filled($data['due_date'] ?? null) ? $data['due_date'] : null;

        return $data;
    }
}
