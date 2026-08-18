@extends('layouts.employee')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')
@section('subheading', 'Overview of your work day, leaves and payslips.')

@section('content')
@php
    $emp = $employee;
    $hour = (int) \App\Support\AppTime::now()->format('G');
    $greet = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
@endphp

@if (! $emp)
    <div class="ep-card"><div class="ep-card__b ep-empty"><strong>Profile not linked</strong>Please ask HR/admin to link your employee record.</div></div>
@else
<section class="ep-hero">
    <div class="ep-hero__eyebrow">Employee workspace</div>
    <h2>{{ $greet }}, {{ explode(' ', $emp->name)[0] }}</h2>
    <p>
        {{ $emp->role_title ?: 'Team member' }}
        @if ($emp->department) · {{ $emp->department->name }} @endif
        · Code {{ $emp->employee_code ?: '—' }}
    </p>
    <div class="ep-hero__actions">
        @if (auth()->user()?->canEmployee('employee.attendance'))
            @if (! $todayRow?->check_in)
                <form method="POST" action="{{ route('employee.attendance.checkin') }}">@csrf
                    <button class="ep-btn ep-btn-primary" type="submit">Check in now</button>
                </form>
            @elseif (! $todayRow?->check_out)
                <form method="POST" action="{{ route('employee.attendance.checkout') }}">@csrf
                    <button class="ep-btn ep-btn-navy" type="submit">Check out</button>
                </form>
                <span class="ep-btn ep-btn-ghost" style="cursor:default">In since {{ \Illuminate\Support\Carbon::parse($todayRow->check_in)->format('h:i A') }}</span>
            @else
                <span class="ep-btn ep-btn-ghost" style="cursor:default">Day complete · Out {{ \Illuminate\Support\Carbon::parse($todayRow->check_out)->format('h:i A') }}</span>
            @endif
        @endif
        @if (auth()->user()?->canEmployee('employee.leaves'))
            <a class="ep-btn ep-btn-ghost" href="{{ route('employee.leaves') }}" style="background:rgba(255,255,255,.12);color:#fff;border-color:transparent">Apply leave</a>
        @endif
    </div>
</section>

@if (! auth()->user()?->hasAnyEmployeeModule())
    <div class="ep-card">
        <div class="ep-card__b ep-empty">
            <strong>No access yet</strong>
            Admin ne aapke role me Employee panel ki koi permission tick nahi ki. Jab tick hogi, menu me wahi pages dikhengi.
        </div>
    </div>
@else
<div class="ep-stats">
    @if (auth()->user()?->canEmployee('employee.attendance'))
    <div class="ep-stat">
        <div class="ep-stat__label">Present this month</div>
        <div class="ep-stat__value">{{ $presentThisMonth }}</div>
        <div class="ep-stat__hint">Attendance days marked present</div>
    </div>
    @endif
    @if (auth()->user()?->canEmployee('employee.leaves'))
    <div class="ep-stat">
        <div class="ep-stat__label">Pending leaves</div>
        <div class="ep-stat__value">{{ $pendingLeaves }}</div>
        <div class="ep-stat__hint">Awaiting HR approval</div>
    </div>
    @endif
    <div class="ep-stat">
        <div class="ep-stat__label">Employment</div>
        <div class="ep-stat__value" style="font-size:1.1rem">{{ $emp->employment_type ?: '—' }}</div>
        <div class="ep-stat__hint">Joined {{ optional($emp->join_date)->format('d M Y') ?: '—' }}</div>
    </div>
    @if (auth()->user()?->canEmployee('employee.interviews'))
    <div class="ep-stat">
        <div class="ep-stat__label">Assigned interviews</div>
        <div class="ep-stat__value">{{ $assignedInterviewCount ?? 0 }}</div>
        <div class="ep-stat__hint"><a href="{{ route('employee.interviews') }}" style="color:#0d9488;font-weight:700;text-decoration:none">Open queue →</a></div>
    </div>
    @endif
    @if (auth()->user()?->canEmployee('employee.tasks'))
    <div class="ep-stat">
        <div class="ep-stat__label">Open tasks</div>
        <div class="ep-stat__value">{{ $openTaskCount ?? 0 }}</div>
        <div class="ep-stat__hint"><a href="{{ route('employee.tasks.index') }}" style="color:#0d9488;font-weight:700;text-decoration:none">View tasks →</a></div>
    </div>
    @endif
    @if (auth()->user()?->canEmployee('employee.payroll'))
    <div class="ep-stat">
        <div class="ep-stat__label">Latest net pay</div>
        <div class="ep-stat__value" style="font-size:1.15rem">
            @if ($latestPay) ₹{{ number_format((float) $latestPay->net_pay, 0) }} @else — @endif
        </div>
        <div class="ep-stat__hint">{{ $latestPay ? $latestPay->monthLabel() : 'No slip yet' }}</div>
    </div>
    @endif
</div>

<div class="ep-grid-2">
    @if (auth()->user()?->canEmployee('employee.attendance'))
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h">
            <h3>Recent attendance</h3>
            <a class="ep-btn ep-btn-ghost" href="{{ route('employee.attendance') }}">View all</a>
        </div>
        <div class="ep-card__b ep-table-wrap">
            <table class="ep-table">
                <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($attendance as $row)
                    <tr>
                        <td>{{ optional($row->date)->format('d M Y') }}</td>
                        <td>{{ $row->check_in ? \Illuminate\Support\Carbon::parse($row->check_in)->format('h:i A') : '—' }}</td>
                        <td>{{ $row->check_out ? \Illuminate\Support\Carbon::parse($row->check_out)->format('h:i A') : '—' }}</td>
                        <td><span class="ep-pill {{ $row->status === 'present' ? 'ep-pill--ok' : 'ep-pill--muted' }}">{{ $row->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="ep-empty">No attendance yet. Use Check in to start today.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Quick actions</h3></div>
        <div class="ep-card__b">
            <div class="ep-quick">
                @if (auth()->user()?->canEmployee('employee.attendance'))
                <a href="{{ route('employee.attendance') }}">
                    <div>Attendance desk<small>Check in / out & history</small></div>
                    <span>→</span>
                </a>
                @endif
                @if (auth()->user()?->canEmployee('employee.leaves'))
                <a href="{{ route('employee.leaves') }}">
                    <div>Request leave<small>Casual, sick or privilege</small></div>
                    <span>→</span>
                </a>
                @endif
                @if (auth()->user()?->canEmployee('employee.interviews'))
                <a href="{{ route('employee.interviews') }}">
                    <div>Assigned interviews<small>{{ $assignedInterviewCount ?? 0 }} pending for you</small></div>
                    <span>→</span>
                </a>
                @endif
                @if (auth()->user()?->canEmployee('employee.payroll'))
                <a href="{{ route('employee.payroll') }}">
                    <div>Payslips<small>Monthly salary records</small></div>
                    <span>→</span>
                </a>
                @endif
                @if (auth()->user()?->canEmployee('employee.tasks'))
                <a href="{{ route('employee.tasks.index') }}">
                    <div>My tasks<small>{{ $openTaskCount ?? 0 }} open from admin</small></div>
                    <span>→</span>
                </a>
                @endif
                @if (auth()->user()?->canEmployee('employee.chat'))
                <a href="{{ route('employee.chat.index') }}">
                    <div>Live chat<small>Talk with admin</small></div>
                    <span>→</span>
                </a>
                @endif
                <a href="{{ route('employee.profile') }}">
                    <div>My profile<small>Details & password</small></div>
                    <span>→</span>
                </a>
            </div>
        </div>
    </div>
</div>

<div class="ep-grid-2">
    @if (auth()->user()?->canEmployee('employee.leaves'))
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h">
            <h3>Leave requests</h3>
            <a class="ep-btn ep-btn-ghost" href="{{ route('employee.leaves') }}">Manage</a>
        </div>
        <div class="ep-card__b ep-table-wrap">
            <table class="ep-table">
                <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($leaves as $row)
                    <tr>
                        <td>{{ $row->leave_type }}</td>
                        <td>{{ optional($row->start_date)->format('d M') }} – {{ optional($row->end_date)->format('d M') }}</td>
                        <td>{{ $row->days }}</td>
                        <td>
                            @php $st = strtolower((string) $row->status); @endphp
                            <span class="ep-pill {{ $st === 'approved' ? 'ep-pill--ok' : ($st === 'pending' ? 'ep-pill--warn' : ($st === 'rejected' ? 'ep-pill--bad' : 'ep-pill--muted')) }}">{{ $row->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="ep-empty">No leave requests yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if (auth()->user()?->canEmployee('employee.payroll'))
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h">
            <h3>Recent payroll</h3>
            <a class="ep-btn ep-btn-ghost" href="{{ route('employee.payroll') }}">All slips</a>
        </div>
        <div class="ep-card__b ep-table-wrap">
            <table class="ep-table">
                <thead><tr><th>Month</th><th>Net</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($payroll as $row)
                    <tr>
                        <td>{{ $row->monthLabel() }}</td>
                        <td>₹{{ number_format((float) $row->net_pay, 2) }}</td>
                        <td><span class="ep-pill {{ $row->status === 'paid' ? 'ep-pill--ok' : 'ep-pill--warn' }}">{{ ucfirst($row->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="3"><div class="ep-empty">No payslips published yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

    @if (auth()->user()?->canEmployee('employee.attendance'))
    <div class="ep-card" style="margin:16px 0 0">
        <div class="ep-card__h"><h3>Attendance graph</h3></div>
        <div class="ep-card__b"><canvas id="empAttChart" height="110"></canvas></div>
    </div>
    <div class="ep-card">
        <div class="ep-card__h">
            <h3>All attendance details</h3>
            <a class="ep-btn ep-btn-ghost" href="{{ route('employee.attendance') }}">Desk</a>
        </div>
        <div class="ep-card__b ep-table-wrap">
            <table class="ep-table">
                <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($allAttendance as $row)
                    <tr>
                        <td>{{ optional($row->date)->format('D, d M Y') }}</td>
                        <td>{{ $row->check_in ? \Illuminate\Support\Carbon::parse($row->check_in)->format('h:i A') : '—' }}</td>
                        <td>{{ $row->check_out ? \Illuminate\Support\Carbon::parse($row->check_out)->format('h:i A') : '—' }}</td>
                        <td><span class="ep-pill {{ $row->status === 'present' ? 'ep-pill--ok' : ($row->status === 'late' ? 'ep-pill--warn' : 'ep-pill--muted') }}">{{ $row->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="ep-empty">No attendance records yet.</div></td></tr>
                @endforelse
                </tbody>
            </table>
            <div style="margin-top:12px">{{ $allAttendance->links() }}</div>
        </div>
    </div>
    @endif
@endif
@endif
@endsection

@push('scripts')
<script>
(() => {
  if (!window.Chart) return;
  const el = document.getElementById('empAttChart');
  if (!el) return;
  new Chart(el, {
    type: 'bar',
    data: {
      labels: @json($attMonthLabels ?? []),
      datasets: [
        { label: 'Present', data: @json($attPresentCounts ?? []), backgroundColor: '#0d9488' },
        { label: 'Late', data: @json($attLateCounts ?? []), backgroundColor: '#f59e0b' }
      ]
    },
    options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
  });
})();
</script>
@endpush
