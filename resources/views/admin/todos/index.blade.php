@extends('layouts.admin')
@section('title', 'To-do List | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>My To-do List</h1>
        <p>Personal checklist for admin work — separate from employee task assignments.</p>
    </div>
</div>

<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ route('admin.todos.store') }}">
            @csrf
            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>To-do *</label>
                    <input type="text" name="title" required maxlength="190" placeholder="e.g. Call client about domain renewal">
                </div>
                <div class="kk-field">
                    <label>Priority *</label>
                    <select name="priority" required>
                        <option value="medium">Medium</option>
                        <option value="high">High</option>
                        <option value="low">Low</option>
                    </select>
                </div>
                <div class="kk-field">
                    <label>Due date</label>
                    <input type="date" name="due_date">
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Notes</label>
                    <input type="text" name="notes" maxlength="2000" placeholder="Optional details">
                </div>
            </div>
            <div class="kk-form-actions">
                <button class="kk-btn kk-btn-primary" type="submit">Add to-do</button>
            </div>
        </form>
    </div>
</section>

<div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;align-items:center;justify-content:space-between">
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        @foreach (['open' => 'Open ('.$counts['open'].')', 'done' => 'Done ('.$counts['done'].')', 'all' => 'All ('.$counts['all'].')'] as $key => $label)
            <a class="kk-btn kk-btn-sm {{ ($filters['filter'] ?? 'open') === $key ? 'kk-btn-primary' : 'kk-btn-secondary' }}" href="{{ route('admin.todos.index', ['filter' => $key, 'q' => $filters['q'] ?? '']) }}">{{ $label }}</a>
        @endforeach
    </div>
    <form method="GET" action="{{ route('admin.todos.index') }}" style="display:flex;gap:8px">
        <input type="hidden" name="filter" value="{{ $filters['filter'] ?? 'open' }}">
        <input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Search to-dos" style="min-width:200px">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-card__body" style="padding:0">
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>To-do</th>
                        <th>Priority</th>
                        <th>Due</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr style="{{ $item->is_done ? 'opacity:.65' : '' }}">
                            <td style="width:48px">
                                <form method="POST" action="{{ route('admin.todos.toggle', $item->id) }}">
                                    @csrf
                                    <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" title="Toggle done">{{ $item->is_done ? '☑' : '☐' }}</button>
                                </form>
                            </td>
                            <td>
                                <strong style="{{ $item->is_done ? 'text-decoration:line-through' : '' }}">{{ $item->title }}</strong>
                                @if ($item->notes)
                                    <div class="kk-muted" style="font-size:12px;margin-top:4px">{{ $item->notes }}</div>
                                @endif
                            </td>
                            <td><span class="kk-pill">{{ ucfirst($item->priority) }}</span></td>
                            <td>{{ optional($item->due_date)->format('d M Y') ?: '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.todos.destroy', $item->id) }}" onsubmit="return confirm('Remove this to-do?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="kk-empty">No to-dos yet. Add one above.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="padding:14px">{{ $items->links() }}</div>
    </div>
</section>
@endsection
