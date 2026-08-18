@extends('layouts.admin')

@section('title', 'Attendance | Admin')

@section('content')
@php
    $fmtTime = function ($t) {
        if (! $t) return '—';
        try { return \Illuminate\Support\Carbon::parse($t)->format('h:i A'); } catch (\Throwable) { return $t; }
    };
    $statusLabel = [
        'present' => 'Present (in)',
        'late' => 'Late',
        'checked_out' => 'Present (out)',
        'leave' => 'Leave',
        'absent' => 'Absent',
        'not_marked' => 'Not marked',
    ];
    $statusClass = [
        'present' => 'kk-pill--hired',
        'late' => 'kk-pill--review',
        'checked_out' => 'kk-pill--open',
        'leave' => 'kk-pill--closed',
        'absent' => 'kk-pill--closed',
        'not_marked' => 'kk-pill--closed',
    ];
@endphp

<div class="kk-pagehead">
    <div>
        <h1>Attendance</h1>
        <p>Sab active employees ki attendance — jo panel se check-in/out karte hain, yahan turant dikhega.</p>
    </div>
</div>

<div class="kk-toolbar" style="flex-wrap:wrap;gap:12px">
    <form method="GET" action="{{ route('admin.attendance.index') }}" class="kk-searchbar" style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;width:100%">
        <input type="month" name="month" value="{{ $month->format('Y-m') }}" style="padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px">
        <select name="employee_id" id="attEmployee" style="flex:1;min-width:260px;height:40px;border:1px solid #cbd5e1;border-radius:10px;padding:0 12px;background:#fff">
            <option value="">All employees — daily board</option>
            @foreach ($allEmployees as $emp)
                <option value="{{ $emp->id }}" @selected((int) $employeeId === (int) $emp->id)>
                    {{ $emp->name }}@if($emp->employee_code) · {{ $emp->employee_code }}@endif · {{ $emp->email }}
                </option>
            @endforeach
        </select>
        <input type="search" name="q" value="{{ $q }}" placeholder="Or type name / email / code…" style="min-width:200px">
        <input type="hidden" name="date" value="{{ $date->toDateString() }}">
        <button class="kk-btn kk-btn-primary" type="submit">View attendance</button>
        <a class="kk-btn kk-btn-ghost" href="{{ route('admin.attendance.index') }}">Reset</a>
    </form>
</div>

@if ($selectedEmployee)
<div style="display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Present</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#0f766e">{{ $monthStats['present'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Late</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#b45309">{{ $monthStats['late'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Leave</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $monthStats['leave'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Absent</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#b91c1c">{{ $monthStats['absent'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Not marked</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $monthStats['not_marked'] }}</div>
    </div></div>
</div>

<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 6px;font-size:16px">{{ $selectedEmployee->name }} — {{ $month->format('F Y') }}</h2>
        <p class="kk-muted" style="margin:0 0 14px;font-size:13px">
            {{ $selectedEmployee->email }}
            @if ($selectedEmployee->employee_code) · {{ $selectedEmployee->employee_code }} @endif
            @if ($selectedEmployee->role_title) · {{ $selectedEmployee->role_title }} @endif
            · Full month attendance
        </p>
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Check in</th>
                        <th>Check out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($monthDays as $day)
                        @php
                            $rec = $day['record'];
                            $st = $day['status'];
                            $d = $day['date'];
                        @endphp
                        <tr style="{{ $day['weekend'] ? 'background:#f8fafc;color:#94a3b8' : '' }}">
                            <td>{{ $d->format('d M Y') }}</td>
                            <td>{{ $d->format('D') }}{{ $day['weekend'] ? ' · Weekend' : '' }}</td>
                            <td>{{ $fmtTime($rec?->check_in) }}</td>
                            <td>{{ $fmtTime($rec?->check_out) }}</td>
                            <td>
                                @if ($day['weekend'] && $st === 'not_marked')
                                    <span class="kk-muted">Off</span>
                                @else
                                    <span class="kk-pill {{ $statusClass[$st] ?? 'kk-pill--closed' }}">{{ $statusLabel[$st] ?? $st }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
@else
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Employees</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $stats['total'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Present / In office</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#0f766e">{{ $stats['present'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">Not marked</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px;color:#b45309">{{ $stats['not_marked'] }}</div>
    </div></div>
    <div class="kk-card"><div class="kk-card__body" style="padding:14px 16px">
        <div class="kk-muted" style="font-size:12px;font-weight:700">On leave</div>
        <div style="font-size:28px;font-weight:800;margin-top:4px">{{ $stats['leave'] }}</div>
    </div></div>
</div>

<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body">
        <h2 style="margin:0 0 6px;font-size:16px">Daily board — {{ $date->format('D, d M Y') }}</h2>
        <p class="kk-muted" style="margin:0 0 14px;font-size:13px">Employee select karke poore month ki attendance dekh sakti ho. Ya yahan aaj ki board dekho.</p>
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Role</th>
                        <th>Check in</th>
                        <th>Check out</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($board as $row)
                        @php $emp = $row['employee']; $rec = $row['record']; $st = $row['status']; @endphp
                        <tr>
                            <td>
                                <strong class="kk-row-title">{{ $emp->name }}</strong>
                                <div class="kk-muted" style="font-size:12px">{{ $emp->email }}@if($emp->employee_code) · {{ $emp->employee_code }}@endif</div>
                            </td>
                            <td>{{ $emp->role_title ?: '—' }}</td>
                            <td>{{ $fmtTime($rec?->check_in) }}</td>
                            <td>{{ $fmtTime($rec?->check_out) }}</td>
                            <td>
                                <span class="kk-pill {{ $statusClass[$st] ?? 'kk-pill--closed' }}">{{ $statusLabel[$st] ?? $st }}</span>
                            </td>
                            <td>
                                <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.attendance.index', ['employee_id' => $emp->id, 'month' => $month->format('Y-m')]) }}">Month</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="kk-empty">
                                    <strong>No active employees</strong>
                                    Pehle Employees me staff add karo — unke panel me Attendance option milta hai.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endif

@unless ($selectedEmployee)
<section class="kk-card">
    <div class="kk-card__body">
        <h2 style="margin:0 0 14px;font-size:16px">All attendance history</h2>
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Check in</th>
                        <th>Check out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($history as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->employee?->name ?? '—' }}</strong>
                                <div class="kk-muted" style="font-size:12px">{{ $item->employee?->email }}</div>
                            </td>
                            <td><span class="kk-muted">{{ optional($item->date)->format('d M Y') }}</span></td>
                            <td>{{ $fmtTime($item->check_in) }}</td>
                            <td>{{ $fmtTime($item->check_out) }}</td>
                            <td><span class="kk-pill kk-pill--{{ $item->status === 'present' ? 'hired' : ($item->status === 'late' ? 'review' : 'closed') }}">{{ $item->status }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="kk-empty">
                                    <strong>No attendance marked yet</strong>
                                    Employees jab panel se Check in karenge, records yahan aa jayenge.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px">{{ $history->links() }}</div>
    </div>
</section>
@endunless

@push('scripts')
<script>
(() => {
  const q = document.querySelector('input[name="q"]');
  const sel = document.getElementById('attEmployee');
  if (!q || !sel) return;
  q.addEventListener('input', () => {
    const needle = (q.value || '').trim().toLowerCase();
    [...sel.options].forEach((opt, i) => {
      if (i === 0) { opt.hidden = false; return; }
      opt.hidden = needle !== '' && !opt.text.toLowerCase().includes(needle);
    });
  });
})();
</script>
@endpush

<style>
@media (max-width: 900px) {
    .kk-pagehead + .kk-toolbar + div[style*="grid-template-columns"] { grid-template-columns: 1fr 1fr !important; }
}
</style>
@endsection
