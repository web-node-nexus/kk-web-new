@extends('layouts.admin')

@section('title', 'Tasks | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Tasks</h1>
        <p>Assign work to all employees or search and pick specific people — it shows in their panel.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.tasks.create') }}" class="kk-btn kk-btn-primary">Assign task</a>
    </div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.tasks.index', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.tasks.index', ['status' => 'open', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'open' ? 'active' : '' }}">Open ({{ $counts['open'] }})</a>
        <a href="{{ route('admin.tasks.index', ['status' => 'in_progress', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'in_progress' ? 'active' : '' }}">In progress ({{ $counts['in_progress'] }})</a>
        <a href="{{ route('admin.tasks.index', ['status' => 'done', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'done' ? 'active' : '' }}">Done ({{ $counts['done'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.tasks.index') }}">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search title / description…">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Assigned to</th>
                    <th>Priority</th>
                    <th>Sent</th>
                    <th>Due</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong class="kk-row-title">{{ $item->title }}</strong>
                            <div class="kk-muted" style="font-size:12px;margin-top:2px">{{ \Illuminate\Support\Str::limit(strip_tags((string) $item->description), 80) ?: 'No description' }}</div>
                        </td>
                        <td>{{ $item->audienceLabel() }} <span class="kk-muted">({{ $item->assignees_count }})</span></td>
                        <td>
                            @php $pri = $item->priority; @endphp
                            <span class="kk-pill {{ $pri === 'high' ? 'kk-pill--rejected' : ($pri === 'low' ? 'kk-pill--draft' : 'kk-pill--review') }}">{{ ucfirst($pri) }}</span>
                        </td>
                        <td><span class="kk-muted">{{ \App\Support\AppTime::formatShort($item->created_at) }}</span></td>
                        <td><span class="kk-muted">{{ $item->dueLabel() }}</span></td>
                        <td>
                            <span class="kk-pill kk-pill--{{ $item->status === 'done' ? 'hired' : ($item->status === 'in_progress' ? 'interview' : 'pending') }}">{{ str_replace('_', ' ', $item->status) }}</span>
                        </td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.tasks.show', $item->id) }}">Open</a>
                                <form method="POST" action="{{ route('admin.tasks.destroy', $item->id) }}" onsubmit="return confirm('Delete this task?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7">
                            <div class="kk-empty">
                                <strong>No tasks yet</strong>
                                “Assign task” se sab employees ko ya kisi ek ko kaam bhejo.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 18px">{{ $items->links() }}</div>
</section>
@endsection
