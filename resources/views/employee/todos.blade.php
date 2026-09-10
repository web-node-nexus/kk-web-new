@extends('layouts.employee')
@section('title', 'My To-do List')
@section('heading', 'My To-do List')
@section('subheading', 'Personal checklist for your work — separate from assigned tasks.')

@section('content')
<div class="ep-card" style="margin-bottom:16px">
    <div class="ep-card__h"><h3>Add to-do</h3></div>
    <div class="ep-card__b">
        <form class="ep-form" method="POST" action="{{ route('employee.todos.store') }}">
            @csrf
            <div class="ep-field">
                <label>Title *</label>
                <input type="text" name="title" required maxlength="190" placeholder="e.g. Prepare weekly report">
            </div>
            <div class="ep-field">
                <label>Priority *</label>
                <select name="priority" required>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="ep-field">
                <label>Due date</label>
                <input type="date" name="due_date">
            </div>
            <div class="ep-field">
                <label>Notes</label>
                <input type="text" name="notes" maxlength="2000">
            </div>
            <button class="ep-btn ep-btn-primary" type="submit">Add to-do</button>
        </form>
    </div>
</div>

<div style="display:flex;gap:8px;margin-bottom:12px;flex-wrap:wrap">
    <a class="ep-btn {{ $filter === 'open' ? 'ep-btn-primary' : '' }}" href="{{ route('employee.todos.index', ['filter' => 'open']) }}">Open ({{ $counts['open'] }})</a>
    <a class="ep-btn {{ $filter === 'done' ? 'ep-btn-primary' : '' }}" href="{{ route('employee.todos.index', ['filter' => 'done']) }}">Done ({{ $counts['done'] }})</a>
    <a class="ep-btn {{ $filter === 'all' ? 'ep-btn-primary' : '' }}" href="{{ route('employee.todos.index', ['filter' => 'all']) }}">All</a>
</div>

<div class="ep-card">
    <div class="ep-card__b" style="display:grid;gap:10px">
        @forelse ($items as $item)
            <div style="display:flex;gap:12px;align-items:flex-start;justify-content:space-between;border:1px solid #e2e8f0;border-radius:12px;padding:12px 14px;{{ $item->is_done ? 'opacity:.7' : '' }}">
                <div>
                    <strong style="{{ $item->is_done ? 'text-decoration:line-through' : '' }}">{{ $item->title }}</strong>
                    <div class="ep-muted" style="font-size:12px;margin-top:4px">
                        {{ ucfirst($item->priority) }}
                        @if ($item->due_date) · due {{ $item->due_date->format('d M Y') }} @endif
                    </div>
                    @if ($item->notes)
                        <p style="margin:6px 0 0;font-size:13px;color:#475569">{{ $item->notes }}</p>
                    @endif
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0">
                    <form method="POST" action="{{ route('employee.todos.toggle', $item->id) }}">
                        @csrf
                        <input type="hidden" name="filter" value="{{ $filter }}">
                        <button class="ep-btn" type="submit">{{ $item->is_done ? 'Undo' : 'Done' }}</button>
                    </form>
                    <form method="POST" action="{{ route('employee.todos.destroy', $item->id) }}" onsubmit="return confirm('Remove this to-do?')">
                        @csrf
                        @method('DELETE')
                        <button class="ep-btn" type="submit">Remove</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="ep-empty">No to-dos yet.</div>
        @endforelse
        <div>{{ $items->links() }}</div>
    </div>
</div>
@endsection
