@extends('layouts.admin')

@section('title', $item->name.' | Employee')

@section('content')
@php $emp = $item; @endphp
<div class="kk-pagehead">
    <div>
        <h1>{{ $emp->name }}</h1>
        <p>{{ $emp->role_title ?: 'Team member' }} · {{ $emp->employee_code ?: 'No code' }} · {{ ucfirst($emp->status) }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.module.edit', ['employees', $emp->id]) }}" class="kk-btn kk-btn-primary">Edit</a>
        <a href="{{ route('admin.module.index', 'employees') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

<div class="kk-grid-2" style="margin-bottom:16px">
    <section class="kk-card">
        <div class="kk-card__body">
            <div style="display:flex;gap:16px;align-items:center;margin-bottom:18px">
                @if ($emp->photoUrl())
                    <img src="{{ $emp->photoUrl() }}" alt="" style="width:88px;height:88px;border-radius:16px;object-fit:cover;border:1px solid #e2e8f0">
                @else
                    @php
                        $ini = collect(explode(' ', $emp->name))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                    @endphp
                    <div style="width:88px;height:88px;border-radius:16px;background:#0f766e;color:#fff;display:flex;align-items:center;justify-content:center;font-size:28px;font-weight:800">{{ $ini ?: 'E' }}</div>
                @endif
                <div>
                    <strong style="display:block;font-size:18px">{{ $emp->name }}</strong>
                    <span class="kk-muted" style="font-size:13px">{{ $emp->email }}</span>
                    <div style="margin-top:8px"><span class="kk-pill kk-pill--{{ $emp->status === 'active' ? 'active' : 'closed' }}">{{ $emp->status }}</span></div>
                </div>
            </div>
            <dl class="kk-kv">
                <dt>Phone</dt><dd>{{ $emp->phone ?: '—' }}</dd>
                <dt>Department</dt><dd>{{ $emp->department->name ?? '—' }}</dd>
                <dt>Role</dt><dd>{{ $emp->role_title ?: '—' }}</dd>
                <dt>Employment type</dt><dd>{{ $emp->employment_type ?: '—' }}</dd>
                <dt>Job type</dt><dd>{{ $emp->job_type ?: '—' }}</dd>
                <dt>Join date</dt><dd>{{ optional($emp->join_date)->format('d M Y') ?: '—' }}</dd>
                <dt>Salary</dt><dd>{{ $emp->salary !== null ? '₹'.number_format((float)$emp->salary, 2) : '—' }}</dd>
                <dt>Panel login</dt><dd>{{ $emp->user?->email ?: 'Not linked' }}</dd>
            </dl>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__body">
            <h2 style="margin:0 0 14px;font-size:16px">Task summary</h2>
            <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px">
                <div style="padding:14px;border:1px solid #e2e8f0;border-radius:12px;background:#f8fafc">
                    <div class="kk-muted" style="font-size:12px;font-weight:700">Total tasks</div>
                    <div style="font-size:22px;font-weight:800;margin-top:6px">{{ $totalTasks }}</div>
                </div>
                <div style="padding:14px;border:1px solid #e2e8f0;border-radius:12px;background:#f0fdf4">
                    <div class="kk-muted" style="font-size:12px;font-weight:700">On time</div>
                    <div style="font-size:22px;font-weight:800;margin-top:6px;color:#15803d">{{ $onTimeTasks }}</div>
                </div>
                <div style="padding:14px;border:1px solid #e2e8f0;border-radius:12px;background:#fef2f2">
                    <div class="kk-muted" style="font-size:12px;font-weight:700">Delayed</div>
                    <div style="font-size:22px;font-weight:800;margin-top:6px;color:#b91c1c">{{ $delayedTasks }}</div>
                </div>
            </div>

            <h2 style="margin:22px 0 10px;font-size:16px">Attendance (6 months)</h2>
            <canvas id="attChart" height="120"></canvas>

            <h2 style="margin:22px 0 10px;font-size:16px">Payroll trend</h2>
            @if (count($payLabels))
                <canvas id="payChart" height="120"></canvas>
            @else
                <p class="kk-muted" style="margin:0">No payroll records yet.</p>
            @endif
        </div>
    </section>
</div>

<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__head"><h2>Recent attendance</h2></div>
    <div class="kk-card__body" style="padding:0">
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Check in</th>
                        <th>Check out</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendance as $row)
                        <tr>
                            <td>{{ optional($row->date)->format('d M Y') }}</td>
                            <td>{{ $row->check_in ?: '—' }}</td>
                            <td>{{ $row->check_out ?: '—' }}</td>
                            <td><span class="kk-pill">{{ $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="kk-empty">No attendance records yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

<section class="kk-card">
    <div class="kk-card__head"><h2>Payroll slips</h2></div>
    <div class="kk-card__body" style="padding:0">
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead>
                    <tr>
                        <th>Month</th>
                        <th>Issue date</th>
                        <th>Net pay</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payroll as $row)
                        <tr>
                            <td>{{ method_exists($row, 'monthLabel') ? $row->monthLabel() : $row->month }}</td>
                            <td>{{ optional($row->issue_date)->format('d M Y') ?: '—' }}</td>
                            <td>₹{{ number_format((float)$row->net_pay, 2) }}</td>
                            <td><span class="kk-pill">{{ $row->status }}</span></td>
                            <td><a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.payroll.receipt', $row->id) }}" target="_blank">Receipt</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="kk-empty">No payroll slips yet.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(() => {
  const att = document.getElementById('attChart');
  if (att && window.Chart) {
    new Chart(att, {
      type: 'bar',
      data: {
        labels: @json($monthLabels),
        datasets: [
          { label: 'Present / Late', data: @json($presentCounts), backgroundColor: '#0f766e' },
          { label: 'Late only', data: @json($lateCounts), backgroundColor: '#f59e0b' },
        ]
      },
      options: { responsive: true, plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
  }
  const pay = document.getElementById('payChart');
  if (pay && window.Chart) {
    new Chart(pay, {
      type: 'line',
      data: {
        labels: @json($payLabels),
        datasets: [{ label: 'Net pay', data: @json($payValues), borderColor: '#0f766e', backgroundColor: 'rgba(15,118,110,.15)', fill: true, tension: .3 }]
      },
      options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }
})();
</script>
@endpush
