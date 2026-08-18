@extends('layouts.employee')
@section('title', 'Payroll')
@section('heading', 'Payroll')
@section('subheading', 'Admin jo salary slip publish karta hai, wahi yahan dikhti hai.')

@section('content')
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $latest = $payroll->first();
@endphp

@if ($latest)
<div class="ep-hero" style="margin-bottom:16px">
    <div class="ep-hero__eyebrow">Latest payslip · {{ $latest->monthLabel() }}</div>
    <h2>{{ $money($latest->net_pay) }}</h2>
    <p>
        {{ $employee->name }}
        @if ($employee->employee_code) · {{ $employee->employee_code }} @endif
        · Status: <strong>{{ ucfirst($latest->status) }}</strong>
        @if ($latest->paid_at)
            · Paid {{ \App\Support\AppTime::formatDate($latest->paid_at) }}
        @endif
    </p>
    <div class="ep-hero__actions">
        <span class="ep-btn ep-btn-ghost" style="cursor:default">Basic {{ $money($latest->basic) }}</span>
        <span class="ep-btn ep-btn-ghost" style="cursor:default">Allowances {{ $money($latest->allowances) }}</span>
        <span class="ep-btn ep-btn-ghost" style="cursor:default">Deductions {{ $money($latest->deductions) }}</span>
    </div>
</div>

<div class="ep-stats" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    <div class="ep-stat">
        <div class="ep-stat__label">Basic</div>
        <div class="ep-stat__value" style="font-size:1.15rem">{{ $money($latest->basic) }}</div>
    </div>
    <div class="ep-stat">
        <div class="ep-stat__label">Allowances</div>
        <div class="ep-stat__value" style="font-size:1.15rem">{{ $money($latest->allowances) }}</div>
    </div>
    <div class="ep-stat">
        <div class="ep-stat__label">Deductions</div>
        <div class="ep-stat__value" style="font-size:1.15rem">{{ $money($latest->deductions) }}</div>
    </div>
    <div class="ep-stat">
        <div class="ep-stat__label">Net pay</div>
        <div class="ep-stat__value" style="font-size:1.15rem;color:#0f766e">{{ $money($latest->net_pay) }}</div>
        <div class="ep-stat__hint">{{ $latest->notes ?: 'Take-home for '.$latest->monthLabel() }}</div>
    </div>
</div>
@else
<div class="ep-card">
    <div class="ep-card__b ep-empty">
        <strong>No payslip yet</strong>
        Jab admin Payroll me aapke naam ka slip add karega, yahan basic, allowances, deductions aur net pay dikhega.
    </div>
</div>
@endif

<div class="ep-card">
    <div class="ep-card__h"><h3>All payslips</h3></div>
    <div class="ep-card__b ep-table-wrap">
        <table class="ep-table">
            <thead>
                <tr>
                    <th>Month</th>
                    <th>Basic</th>
                    <th>Allowances</th>
                    <th>Deductions</th>
                    <th>Net pay</th>
                    <th>Status</th>
                    <th>Paid on</th>
                    <th>Notes</th>
                </tr>
            </thead>
            <tbody>
            @forelse ($payroll as $row)
                @php $st = strtolower((string) $row->status); @endphp
                <tr>
                    <td><strong>{{ $row->monthLabel() }}</strong></td>
                    <td>{{ $money($row->basic) }}</td>
                    <td>{{ $money($row->allowances) }}</td>
                    <td>{{ $money($row->deductions) }}</td>
                    <td><strong>{{ $money($row->net_pay) }}</strong></td>
                    <td>
                        <span class="ep-pill {{ $st === 'paid' ? 'ep-pill--ok' : 'ep-pill--warn' }}">{{ ucfirst($row->status) }}</span>
                    </td>
                    <td>{{ $row->paid_at ? \App\Support\AppTime::formatDate($row->paid_at) : '—' }}</td>
                    <td>{{ $row->notes ? \Illuminate\Support\Str::limit($row->notes, 50) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        <div class="ep-empty">Abhi koi slip nahi hai.</div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $payroll->links() }}</div>
    </div>
</div>
@endsection
