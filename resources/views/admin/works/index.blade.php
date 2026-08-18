@extends('layouts.admin')

@section('title', 'Projects | Admin')

@section('content')
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
@endphp

<div class="kk-pagehead">
    <div>
        <h1>Projects</h1>
        <p>Client projects, budget, received / pending amount, EMI aur receipts — yahi se manage karo.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.works.create') }}" class="kk-btn kk-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Add project
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Projects</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $counts['all'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Received</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px;color:#0f766e">{{ $money($totals['received']) }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Pending</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px;color:#b45309">{{ $money($totals['pending']) }}</div>
    </div></div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.works.index', ['status' => 'all', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.works.index', ['status' => 'active', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'active' ? 'active' : '' }}">Active ({{ $counts['active'] }})</a>
        <a href="{{ route('admin.works.index', ['status' => 'completed', 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'completed' ? 'active' : '' }}">Completed ({{ $counts['completed'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.works.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search project / client / email…">
        <button class="kk-btn kk-btn-secondary" type="submit">Filter</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>Project</th>
                    <th>Client</th>
                    <th>Due</th>
                    <th>Budget</th>
                    <th>Received</th>
                    <th>Pending</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    @php
                        $received = $item->receivedAmount();
                        $pending = $item->pendingAmount();
                    @endphp
                    <tr>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                @if ($item->logoUrl())
                                    <img src="{{ $item->logoUrl() }}" alt="" style="width:36px;height:36px;border-radius:8px;object-fit:cover;border:1px solid #e2e8f0">
                                @endif
                                <div>
                                    <strong class="kk-row-title">{{ $item->name }}</strong>
                                    <div class="kk-muted" style="font-size:12px">{{ $item->code() }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong>{{ $item->client_name }}</strong>
                            <div class="kk-muted" style="font-size:12px">{{ $item->mobile }} · {{ $item->email }}</div>
                        </td>
                        <td>{{ $item->due_date?->format('d M Y') ?: '—' }}</td>
                        <td>{{ $money($item->final_budget) }}</td>
                        <td style="color:#0f766e;font-weight:700">{{ $money($received) }}</td>
                        <td style="color:#b45309;font-weight:700">{{ $money($pending) }}</td>
                        <td><span class="kk-pill kk-pill--{{ $item->status === 'completed' ? 'hired' : 'review' }}">{{ $item->status }}</span></td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                <a class="kk-btn kk-btn-primary kk-btn-sm" href="{{ route('admin.works.show', $item->id) }}">Open</a>
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.works.edit', $item->id) }}">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="kk-empty">
                                <strong>No projects yet</strong>
                                “Add project” se client, budget, EMI aur receipt daalo.
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
