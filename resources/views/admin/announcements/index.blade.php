@extends('layouts.admin')

@section('title', 'Announcements | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Announcements</h1>
        <p>Send a message to all employees or one employee — it appears in their panel.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.announcements.create') }}" class="kk-btn kk-btn-primary">New announcement</a>
    </div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.announcements.index', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.announcements.index', ['status' => 'published', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'published' ? 'active' : '' }}">Published ({{ $counts['published'] }})</a>
        <a href="{{ route('admin.announcements.index', ['status' => 'draft', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'draft' ? 'active' : '' }}">Draft ({{ $counts['draft'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.announcements.index') }}">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search title / message…">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Send to</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong class="kk-row-title">{{ $item->title }}</strong>
                            <div class="kk-muted" style="font-size:12px;margin-top:2px">{{ \Illuminate\Support\Str::limit(strip_tags($item->body), 80) }}</div>
                        </td>
                        <td>{{ $item->audienceLabel() }}</td>
                        <td><span class="kk-pill kk-pill--{{ $item->status === 'published' ? 'hired' : 'review' }}">{{ $item->status }}</span></td>
                        <td><span class="kk-muted">{{ $item->published_at?->timezone(config('app.timezone','Asia/Kolkata'))->format('d M Y, h:i A') ?: '—' }}</span></td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                @if ($item->status !== 'published')
                                    <form method="POST" action="{{ route('admin.announcements.publish', $item->id) }}">
                                        @csrf
                                        <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit">Publish</button>
                                    </form>
                                @endif
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.announcements.edit', $item->id) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.announcements.destroy', $item->id) }}" onsubmit="return confirm('Delete this announcement?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">
                            <div class="kk-empty">
                                <strong>No announcements yet</strong>
                                “New announcement” se sabko ya kisi ek employee ko message bhejo.
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
