@extends('layouts.admin')
@section('title', 'Reports | Admin')

@section('content')
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 0);
@endphp

<div class="kk-pagehead">
    <div>
        <h1>Reports</h1>
        <p>Live hiring, HR & payroll insights — updated {{ $generatedAt }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a class="kk-btn kk-btn-secondary" href="{{ route('admin.reports.export') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
            Export Report
        </a>
    </div>
</div>

<div class="kk-stats" style="margin-bottom:16px">
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Applications</p>
        <div class="kk-stat__value">{{ number_format($kpis['applications']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Hired</p>
        <div class="kk-stat__value">{{ number_format($kpis['hired']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Active employees</p>
        <div class="kk-stat__value">{{ number_format($kpis['employees']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Present today</p>
        <div class="kk-stat__value">{{ number_format($kpis['present_today']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Pending leaves</p>
        <div class="kk-stat__value">{{ number_format($kpis['pending_leaves']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Open jobs</p>
        <div class="kk-stat__value">{{ number_format($kpis['open_jobs']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">New contacts</p>
        <div class="kk-stat__value">{{ number_format($kpis['contacts_new']) }}</div>
    </article>
    <article class="kk-card kk-stat">
        <p class="kk-stat__label">Payroll paid</p>
        <div class="kk-stat__value" style="font-size:1.25rem">{{ $money($kpis['payroll_paid']) }}</div>
    </article>
</div>

<div class="kk-grid-2eq" style="margin-bottom:16px">
    <section class="kk-card">
        <div class="kk-card__head"><h2>Applications — last 30 days</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box"><canvas id="rptAppsTrend"></canvas></div></div>
    </section>
    <section class="kk-card">
        <div class="kk-card__head"><h2>Applications by status</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box"><canvas id="rptAppsStatus"></canvas></div></div>
    </section>
</div>

<div class="kk-grid-2eq" style="margin-bottom:16px">
    <section class="kk-card">
        <div class="kk-card__head"><h2>Applications by source</h2></div>
        <div class="kk-card__body">
            <div class="kk-donut-wrap">
                <div class="kk-chart-box kk-chart-box--sm"><canvas id="rptAppsSource"></canvas></div>
            </div>
        </div>
    </section>
    <section class="kk-card">
        <div class="kk-card__head"><h2>Attendance (present) — 14 days</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box"><canvas id="rptAttendance"></canvas></div></div>
    </section>
</div>

<div class="kk-grid-2eq" style="margin-bottom:16px">
    <section class="kk-card">
        <div class="kk-card__head"><h2>Leave requests</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box kk-chart-box--sm"><canvas id="rptLeaves"></canvas></div></div>
    </section>
    <section class="kk-card">
        <div class="kk-card__head"><h2>Payroll amount ({{ $monthLabel }} context)</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box kk-chart-box--sm"><canvas id="rptPayroll"></canvas></div></div>
    </section>
</div>

<div class="kk-grid-2eq" style="margin-bottom:16px">
    <section class="kk-card">
        <div class="kk-card__head"><h2>Interviews by status</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box kk-chart-box--sm"><canvas id="rptInterviews"></canvas></div></div>
    </section>
    <section class="kk-card">
        <div class="kk-card__head"><h2>Employees by department</h2></div>
        <div class="kk-card__body"><div class="kk-chart-box"><canvas id="rptDepartments"></canvas></div></div>
    </section>
</div>

<section class="kk-card">
    <div class="kk-card__head"><h2>Workforce status</h2></div>
    <div class="kk-card__body"><div class="kk-chart-box kk-chart-box--sm" style="max-width:420px;margin:0 auto"><canvas id="rptWorkforce"></canvas></div></div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!window.Chart) return;

  const charts = @json($charts);
  const palette = ['#2563eb','#0d9488','#f59e0b','#8b5cf6','#ef4444','#64748b','#06b6d4','#84cc16'];
  const soft = (hex, a=0.18) => {
    const h = hex.replace('#','');
    const n = parseInt(h, 16);
    const r = (n >> 16) & 255, g = (n >> 8) & 255, b = n & 255;
    return `rgba(${r},${g},${b},${a})`;
  };

  const baseOpts = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { boxWidth: 12, usePointStyle: true, padding: 16 } },
      tooltip: { backgroundColor: '#0f172a', padding: 10, cornerRadius: 8 }
    }
  };

  const gridScale = {
    x: { grid: { display: false }, ticks: { color: '#64748b', maxRotation: 0 } },
    y: { beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { color: '#64748b', precision: 0 } }
  };

  // Line — applications trend
  new Chart(document.getElementById('rptAppsTrend'), {
    type: 'line',
    data: {
      labels: charts.appsTrend.labels,
      datasets: [{
        label: 'Applications',
        data: charts.appsTrend.data,
        borderColor: '#2563eb',
        backgroundColor: soft('#2563eb', 0.15),
        fill: true,
        tension: 0.35,
        pointRadius: 2,
        pointHoverRadius: 5,
        borderWidth: 2.5
      }]
    },
    options: { ...baseOpts, plugins: { ...baseOpts.plugins, legend: { display: false } }, scales: gridScale }
  });

  // Bar — status
  const statusLabels = Object.keys(charts.appsByStatus);
  new Chart(document.getElementById('rptAppsStatus'), {
    type: 'bar',
    data: {
      labels: statusLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
      datasets: [{
        data: Object.values(charts.appsByStatus),
        backgroundColor: statusLabels.map((_, i) => palette[i % palette.length]),
        borderRadius: 8,
        barThickness: 28
      }]
    },
    options: { ...baseOpts, plugins: { ...baseOpts.plugins, legend: { display: false } }, scales: gridScale }
  });

  // Doughnut — source
  const srcLabels = Object.keys(charts.appsBySource);
  new Chart(document.getElementById('rptAppsSource'), {
    type: 'doughnut',
    data: {
      labels: srcLabels,
      datasets: [{
        data: Object.values(charts.appsBySource),
        backgroundColor: srcLabels.map((_, i) => palette[i % palette.length]),
        borderWidth: 0,
        hoverOffset: 6
      }]
    },
    options: { ...baseOpts, cutout: '62%' }
  });

  // Bar — attendance
  new Chart(document.getElementById('rptAttendance'), {
    type: 'bar',
    data: {
      labels: charts.attendance.labels,
      datasets: [{
        label: 'Present / Late',
        data: charts.attendance.data,
        backgroundColor: '#0d9488',
        borderRadius: 6,
        barThickness: 16
      }]
    },
    options: { ...baseOpts, plugins: { ...baseOpts.plugins, legend: { display: false } }, scales: gridScale }
  });

  // Doughnut — leaves
  new Chart(document.getElementById('rptLeaves'), {
    type: 'doughnut',
    data: {
      labels: ['Pending', 'Approved', 'Rejected'],
      datasets: [{
        data: [charts.leaveByStatus.pending, charts.leaveByStatus.approved, charts.leaveByStatus.rejected],
        backgroundColor: ['#f59e0b', '#0d9488', '#ef4444'],
        borderWidth: 0
      }]
    },
    options: { ...baseOpts, cutout: '58%' }
  });

  // Bar — payroll amounts
  new Chart(document.getElementById('rptPayroll'), {
    type: 'bar',
    data: {
      labels: ['Pending', 'Paid'],
      datasets: [{
        label: 'Net pay (₹)',
        data: [charts.payroll.pending, charts.payroll.paid],
        backgroundColor: ['#f59e0b', '#2563eb'],
        borderRadius: 10,
        barThickness: 48
      }]
    },
    options: {
      ...baseOpts,
      plugins: { ...baseOpts.plugins, legend: { display: false } },
      scales: {
        x: { grid: { display: false } },
        y: {
          beginAtZero: true,
          grid: { color: '#eef2f7' },
          ticks: {
            callback: (v) => '₹' + Number(v).toLocaleString('en-IN')
          }
        }
      }
    }
  });

  // Interviews
  const intLabels = Object.keys(charts.interviews);
  new Chart(document.getElementById('rptInterviews'), {
    type: 'bar',
    data: {
      labels: intLabels.map(s => s.charAt(0).toUpperCase() + s.slice(1)),
      datasets: [{
        data: Object.values(charts.interviews),
        backgroundColor: '#8b5cf6',
        borderRadius: 8,
        barThickness: 32
      }]
    },
    options: { ...baseOpts, indexAxis: 'y', plugins: { ...baseOpts.plugins, legend: { display: false } }, scales: {
      x: { beginAtZero: true, grid: { color: '#eef2f7' }, ticks: { precision: 0 } },
      y: { grid: { display: false } }
    }}
  });

  // Departments
  const deptLabels = Object.keys(charts.departments);
  new Chart(document.getElementById('rptDepartments'), {
    type: 'bar',
    data: {
      labels: deptLabels,
      datasets: [{
        data: Object.values(charts.departments),
        backgroundColor: deptLabels.map((_, i) => palette[i % palette.length]),
        borderRadius: 8,
        barThickness: 26
      }]
    },
    options: { ...baseOpts, plugins: { ...baseOpts.plugins, legend: { display: false } }, scales: gridScale }
  });

  // Workforce
  new Chart(document.getElementById('rptWorkforce'), {
    type: 'doughnut',
    data: {
      labels: ['Active', 'Inactive'],
      datasets: [{
        data: [charts.employees.active, charts.employees.inactive],
        backgroundColor: ['#0d9488', '#94a3b8'],
        borderWidth: 0
      }]
    },
    options: { ...baseOpts, cutout: '60%' }
  });
});
</script>
@endpush
