@extends('layouts.admin')

@section('title', 'Job Applications | Admin')
@section('eyebrow', 'Hiring')
@section('heading', 'Job Applications')

@section('actions')
    <a href="{{ route('careers.apply') }}" target="_blank" rel="noopener" class="tn-btn tn-btn-outline !py-2.5 !px-4 text-sm">Open Apply Form</a>
@endsection

@section('content')
<div class="admin-filters">
    <a href="{{ route('admin.applications', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All <em>{{ $counts['all'] }}</em></a>
    <a href="{{ route('admin.applications', ['status' => 'new', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'new' ? 'active' : '' }}">New <em>{{ $counts['new'] }}</em></a>
    <a href="{{ route('admin.applications', ['status' => 'reviewed', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'reviewed' ? 'active' : '' }}">Reviewed <em>{{ $counts['reviewed'] }}</em></a>
    <a href="{{ route('admin.applications', ['status' => 'shortlisted', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'shortlisted' ? 'active' : '' }}">Shortlisted <em>{{ $counts['shortlisted'] }}</em></a>
    <a href="{{ route('admin.applications', ['status' => 'rejected', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'rejected' ? 'active' : '' }}">Rejected <em>{{ $counts['rejected'] }}</em></a>
</div>

<form method="GET" action="{{ route('admin.applications') }}" class="admin-search">
    <input type="hidden" name="status" value="{{ $filters['status'] }}">
    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search name, email, phone or position..." class="form-input">
    <button type="submit" class="tn-btn tn-btn-primary !py-2.5 !px-4 text-sm">Search</button>
</form>

<div class="admin-panel">
    @forelse ($applications as $app)
        <a href="{{ route('admin.applications.show', $app) }}" class="admin-app-card">
            <div class="admin-app-card__top">
                <div>
                    <strong>{{ $app->full_name }}</strong>
                    <p>{{ $app->position }} · {{ $app->department }} · {{ $app->job_type }}</p>
                </div>
                <em class="admin-status admin-status--{{ $app->status }}">{{ $app->status }}</em>
            </div>
            <div class="admin-app-card__meta">
                <span>{{ $app->email }}</span>
                <span>{{ $app->phone }}</span>
                <span>{{ $app->created_at?->format('d M Y, h:i A') }}</span>
            </div>
        </a>
    @empty
        <p class="admin-empty">No applications found for this filter.</p>
    @endforelse
</div>

<div class="admin-pagination">
    {{ $applications->links() }}
</div>
@endsection
