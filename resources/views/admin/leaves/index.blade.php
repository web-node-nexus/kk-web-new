@extends('layouts.admin')

@section('title', 'Leave Requests | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Leave Requests</h1>
        <p>Employees panel se leave + reason daalte hain — yahan Approve / Reject karo. Approve hone par employee ko email jayega.</p>
    </div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.leaves.index', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.leaves.index', ['status' => 'pending', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'pending' ? 'active' : '' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('admin.leaves.index', ['status' => 'approved', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'approved' ? 'active' : '' }}">Approved ({{ $counts['approved'] }})</a>
        <a href="{{ route('admin.leaves.index', ['status' => 'rejected', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'rejected' ? 'active' : '' }}">Rejected ({{ $counts['rejected'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.leaves.index') }}">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search employee / type / reason…">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Type</th>
                    <th>From</th>
                    <th>To</th>
                    <th>Days</th>
                    <th>Reason</th>
                    <th>Status</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong class="kk-row-title">{{ $item->employee?->name ?? '—' }}</strong>
                            <div class="kk-muted" style="font-size:12px">{{ $item->employee?->email }}</div>
                        </td>
                        <td>{{ $item->leave_type }}</td>
                        <td><span class="kk-muted">{{ optional($item->start_date)->format('d M Y') }}</span></td>
                        <td><span class="kk-muted">{{ optional($item->end_date)->format('d M Y') }}</span></td>
                        <td>{{ $item->days }}</td>
                        <td style="max-width:260px">
                            <span title="{{ $item->reason }}">{{ \Illuminate\Support\Str::limit($item->reason, 80) ?: '—' }}</span>
                        </td>
                        <td>
                            <span class="kk-pill kk-pill--{{ $item->status === 'approved' ? 'hired' : ($item->status === 'pending' ? 'review' : 'closed') }}">{{ $item->status }}</span>
                        </td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                @if ($item->status === 'pending')
                                    <form method="POST" action="{{ route('admin.leaves.decide', $item->id) }}">
                                        @csrf
                                        <input type="hidden" name="status" value="approved">
                                        <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit" onclick="return confirm('Approve this leave? Employee will get email.')">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.leaves.decide', $item->id) }}" onsubmit="return confirm('Reject this leave? Employee will get email.')">
                                        @csrf
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Reject</button>
                                    </form>
                                @else
                                    <span class="kk-muted" style="font-size:12px">Already {{ $item->status }}</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="kk-empty">
                                <strong>No leave requests</strong>
                                Jab koi employee panel se leave apply karega (reason ke saath), yahan pending me dikhega.
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
