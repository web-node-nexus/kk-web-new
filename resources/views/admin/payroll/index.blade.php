@extends('layouts.admin')

@section('title', 'Payroll | Admin')

@section('content')
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
@endphp

<div class="kk-pagehead">
    <div>
        <h1>Payroll</h1>
        <p>Choose an employee and save the slip — it appears in their Employee panel → Payroll. You can also attach a receipt file.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.payroll.create') }}" class="kk-btn kk-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Add payroll
        </a>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Total slips</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $counts['all'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Pending</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#b45309">{{ $counts['pending'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Paid (all time)</div>
        <div style="font-size:22px;font-weight:800;margin-top:6px;color:#0f766e">{{ $money($totals['paid_net']) }}</div>
    </div></div>
</div>

<div class="kk-toolbar">
    <div class="kk-filters">
        <a href="{{ route('admin.payroll.index', ['status' => 'all', 'q' => $filters['q'], 'month' => $filters['month']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] }})</a>
        <a href="{{ route('admin.payroll.index', ['status' => 'pending', 'q' => $filters['q'], 'month' => $filters['month']]) }}" class="{{ $filters['status'] === 'pending' ? 'active' : '' }}">Pending ({{ $counts['pending'] }})</a>
        <a href="{{ route('admin.payroll.index', ['status' => 'paid', 'q' => $filters['q'], 'month' => $filters['month']]) }}" class="{{ $filters['status'] === 'paid' ? 'active' : '' }}">Paid ({{ $counts['paid'] }})</a>
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.payroll.index') }}" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="month" name="month" value="{{ $filters['month'] }}" style="padding:8px 10px;border:1px solid #cbd5e1;border-radius:10px">
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search employee…">
        <button class="kk-btn kk-btn-secondary" type="submit">Filter</button>
    </form>
</div>

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Month</th>
                    <th>Basic</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th>Net pay</th>
                    <th>Status</th>
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>
                            <strong class="kk-row-title">{{ $item->employee?->name ?? '—' }}</strong>
                            <div class="kk-muted" style="font-size:12px">{{ $item->employee?->email }}@if($item->employee?->employee_code) · {{ $item->employee->employee_code }}@endif</div>
                        </td>
                        <td>{{ $item->monthLabel() }}</td>
                        <td>{{ $money($item->basic) }}</td>
                        <td>{{ $money($item->allowances) }}</td>
                        <td>{{ $money($item->deductions) }}</td>
                        <td><strong>{{ $money($item->net_pay) }}</strong></td>
                        <td>
                            <span class="kk-pill kk-pill--{{ $item->status === 'paid' ? 'hired' : 'review' }}">{{ $item->status }}</span>
                            @if ($item->paid_at)
                                <div class="kk-muted" style="font-size:11px;margin-top:4px">{{ \App\Support\AppTime::formatDate($item->paid_at) }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="kk-actions" style="justify-content:flex-end">
                                @if ($item->status !== 'paid')
                                    <form method="POST" action="{{ route('admin.payroll.mark-paid', $item->id) }}">
                                        @csrf
                                        <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit">Mark paid</button>
                                    </form>
                                @endif
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.payroll.receipt', $item->id) }}" target="_blank">Receipt</a>
                                @if ($item->hasUploadedReceipt())
                                    <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ $item->receiptFileUrl() }}" target="_blank">Proof</a>
                                @endif
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.payroll.edit', $item->id) }}">Edit</a>
                                <form method="POST" action="{{ route('admin.payroll.destroy', $item->id) }}" onsubmit="return confirm('Delete this payroll slip?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">
                            <div class="kk-empty">
                                <strong>No payroll yet</strong>
                                “Add payroll” se employee choose karke basic / allowances / deductions daalo.
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:14px 18px">{{ $items->links() }}</div>
</section>

<style>
@media (max-width: 900px) {
    .kk-pagehead + div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
@endsection
