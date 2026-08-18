@extends('layouts.admin')

@section('title', 'Contact Inquiries | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Contact Inquiries</h1>
        <p>Website <strong>Contact</strong> page se jo bhi message / query aaye — yahan turant dikhega.</p>
    </div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.contacts.index', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.contacts.index', ['status' => 'new', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'new' ? 'active' : '' }}">New ({{ $counts['new'] }})</a>
        <a href="{{ route('admin.contacts.index', ['status' => 'reviewed', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'reviewed' ? 'active' : '' }}">Reviewed ({{ $counts['reviewed'] }})</a>
        <a href="{{ route('admin.contacts.index', ['status' => 'closed', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'closed' ? 'active' : '' }}">Closed ({{ $counts['closed'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.contacts.index') }}">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, email, message…">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>From</th>
                    <th>Service</th>
                    <th>Message</th>
                    <th>Received</th>
                    <th>Status</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr style="{{ $item->status === 'new' ? 'background:#f0f9ff' : '' }}">
                        <td>
                            <strong class="kk-row-title">{{ $item->name }}</strong>
                            <div class="kk-muted" style="font-size:12px">
                                <a href="mailto:{{ $item->email }}" style="color:inherit">{{ $item->email }}</a>
                                @if ($item->phone) · {{ $item->phone }}@endif
                            </div>
                            @if ($item->company)
                                <div class="kk-muted" style="font-size:12px">{{ $item->company }}</div>
                            @endif
                        </td>
                        <td>{{ $item->service ?: '—' }}</td>
                        <td style="max-width:320px">
                            <span title="{{ $item->message }}">{{ \Illuminate\Support\Str::limit($item->message, 90) }}</span>
                        </td>
                        <td>
                            <span class="kk-muted">{{ $item->created_at?->timezone(config('app.timezone','Asia/Kolkata'))->format('d M Y, h:i A') }}</span>
                        </td>
                        <td>
                            <span class="kk-pill kk-pill--{{ $item->status === 'new' ? 'review' : ($item->status === 'reviewed' ? 'open' : 'closed') }}">{{ $item->status }}</span>
                        </td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                <a class="kk-btn kk-btn-primary kk-btn-sm" href="{{ route('admin.contacts.show', $item->id) }}">Open</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="kk-empty">
                                <strong>No contact messages yet</strong>
                                Jab koi website Contact form se query bhejega, yahan New me dikhega.
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
