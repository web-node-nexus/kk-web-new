@extends('layouts.admin')

@section('title', 'Dashboard | KK Digital Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Welcome back, KK Digital Solution Admin</h1>
        <p>Here's what's happening with your career portal today.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a class="kk-btn kk-btn-secondary" href="{{ route('admin.reports.export') }}">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/></svg>
            Export Report
        </a>
        <div class="kk-datepill">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
            {{ $todayLabel }}
        </div>
    </div>
</div>

<div class="kk-stats">
    <article class="kk-card kk-stat">
        <div class="kk-stat__icon kk-stat__icon--blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>
        </div>
        <p class="kk-stat__label">Total Applications</p>
        <div class="kk-stat__value">{{ number_format($stats['total']) }}</div>
        <div class="kk-stat__trend">+18.5% <span>from last month</span></div>
        <canvas id="sparkTotal"></canvas>
    </article>
    <article class="kk-card kk-stat">
        <div class="kk-stat__icon kk-stat__icon--green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        </div>
        <p class="kk-stat__label">New Applications</p>
        <div class="kk-stat__value">{{ number_format($stats['new']) }}</div>
        <div class="kk-stat__trend">+12.3% <span>from last week</span></div>
        <canvas id="sparkNew"></canvas>
    </article>
    <article class="kk-card kk-stat">
        <div class="kk-stat__icon kk-stat__icon--orange">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
        </div>
        <p class="kk-stat__label">Open Positions</p>
        <div class="kk-stat__value">{{ number_format($stats['open']) }}</div>
        <div class="kk-stat__trend">+3 <span>new this week</span></div>
        <canvas id="sparkOpen"></canvas>
    </article>
    <article class="kk-card kk-stat">
        <div class="kk-stat__icon kk-stat__icon--purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/></svg>
        </div>
        <p class="kk-stat__label">Total Hired</p>
        <div class="kk-stat__value">{{ number_format($stats['hired']) }}</div>
        <div class="kk-stat__trend">+8.2% <span>from last month</span></div>
        <canvas id="sparkHired"></canvas>
    </article>
</div>

<div class="kk-grid-3">
    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Applications Overview</h2>
            <button class="kk-btn kk-btn-secondary kk-btn-sm" type="button">This Month</button>
        </div>
        <div class="kk-card__body">
            <div class="kk-chart-box"><canvas id="overviewChart"></canvas></div>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Applications by Source</h2>
        </div>
        <div class="kk-card__body">
            <div class="kk-donut-wrap">
                <div class="kk-chart-box kk-chart-box--sm"><canvas id="sourceChart"></canvas></div>
                <ul class="kk-legend">
                    @php
                        $colors = ['#2563eb','#22c55e','#f59e0b','#8b5cf6','#94a3b8'];
                        $i = 0;
                        $sourceTotal = max(1, array_sum($sources));
                    @endphp
                    @foreach ($sources as $label => $val)
                        <li>
                            <span class="kk-legend__left"><i style="background:{{ $colors[$i % count($colors)] }}"></i>{{ $label }}</span>
                            <strong>{{ round(($val / $sourceTotal) * 100) }}%</strong>
                        </li>
                        @php $i++; @endphp
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Recent Activity</h2>
        </div>
        <div class="kk-card__body" style="padding-top:6px">
            <ul class="kk-activity">
                @forelse ($activities as $act)
                    @php
                        $bg = match($act['tone']) {
                            'green' => '#ecfdf5', 'purple' => '#f5f3ff', 'orange' => '#fffbeb', default => '#eff6ff'
                        };
                        $fg = match($act['tone']) {
                            'green' => '#16a34a', 'purple' => '#7c3aed', 'orange' => '#d97706', default => '#2563eb'
                        };
                    @endphp
                    <li>
                        <div class="kk-activity__dot" style="background:{{ $bg }};color:{{ $fg }}">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg>
                        </div>
                        <div>
                            @if (!empty($act['href']))
                                <a href="{{ $act['href'] }}"><strong>{{ $act['title'] }}</strong></a>
                            @else
                                <strong>{{ $act['title'] }}</strong>
                            @endif
                            <p>{{ $act['desc'] }}</p>
                        </div>
                        <time>{{ $act['time'] }}</time>
                    </li>
                @empty
                    <li><div></div><div><strong>No recent activity</strong><p>Website contact, project and job applications will appear here instantly.</p></div><time></time></li>
                @endforelse
            </ul>
        </div>
        <div class="kk-card__foot" style="display:flex;gap:14px;flex-wrap:wrap">
            <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'applications') }}">Applications →</a>
            <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'contacts') }}">Contacts →</a>
            <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'projects') }}">Projects →</a>
        </div>
    </section>
</div>

<div class="kk-grid-3b">
    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Recent Job Applications</h2>
            <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'applications') }}">View All</a>
        </div>
        <div class="kk-card__body" style="padding-top:4px">
            <ul class="kk-applist">
                @forelse ($recentApps as $app)
                    @php
                        $palette = ['#2563eb','#16a34a','#f59e0b','#8b5cf6','#06b6d4'];
                        $color = $palette[$loop->index % count($palette)];
                        $status = $app->status === 'reviewed' ? 'review' : $app->status;
                    @endphp
                    <li>
                        <div class="kk-avatar" style="background:{{ $color }}">{{ strtoupper(mb_substr($app->full_name, 0, 1)) }}</div>
                        <div class="kk-applist__meta">
                            <strong>{{ $app->full_name }}</strong>
                            <span>{{ $app->position }}</span>
                        </div>
                        <time>{{ $app->created_at?->diffForHumans() }}</time>
                        <span class="kk-pill kk-pill--{{ $status }}">{{ $status }}</span>
                    </li>
                @empty
                    <li><div class="kk-applist__meta"><strong>No applications yet</strong><span>Candidates will appear here after applying.</span></div></li>
                @endforelse
            </ul>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Top Job Openings</h2>
            <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'jobs') }}">Manage</a>
        </div>
        <div class="kk-card__body" style="padding-top:4px">
            <ul class="kk-joblist">
                @forelse ($topJobs as $job)
                    <li>
                        <div>
                            <strong>{{ $job->title }}</strong>
                            <span>{{ $job->department_name ?: ($job->employment_type ?: 'General') }}</span>
                        </div>
                        <em>{{ $job->applications_count }} apps</em>
                    </li>
                @empty
                    <li><div><strong>No openings</strong><span>Add roles from Job Openings.</span></div></li>
                @endforelse
            </ul>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head"><h2>Quick Actions</h2></div>
        <div class="kk-card__body">
            <div class="kk-quick">
                <a href="{{ route('admin.module.create', 'jobs') }}">
                    <span class="kk-quick__icon" style="background:#eff6ff;color:#2563eb"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg></span>
                    Add Job Opening
                </a>
                <a href="{{ route('admin.module.index', 'applications') }}">
                    <span class="kk-quick__icon" style="background:#ecfdf5;color:#16a34a"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/></svg></span>
                    View Applications
                </a>
                <a href="{{ route('admin.module.create', 'employees') }}">
                    <span class="kk-quick__icon" style="background:#f5f3ff;color:#7c3aed"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/></svg></span>
                    Add Employee
                </a>
                <a href="{{ route('admin.career-page') }}">
                    <span class="kk-quick__icon" style="background:#fffbeb;color:#d97706"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/></svg></span>
                    Career Page
                </a>
            </div>
        </div>
    </section>
</div>

<div class="kk-grid-2">
    <section class="kk-card">
        <div class="kk-card__head">
            <h2>Application Status Overview</h2>
        </div>
        <div class="kk-card__body">
            <div class="kk-chart-box"><canvas id="statusChart"></canvas></div>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head"><h2>Status Summary</h2></div>
        <div class="kk-card__body">
            <div class="kk-status-grid">
                <div>
                    @php
                        $total = max(1, array_sum($statusCounts));
                        $map = [
                            'new' => ['New', '#2563eb'],
                            'reviewed' => ['Review', '#f59e0b'],
                            'shortlisted' => ['Shortlisted', '#22c55e'],
                            'interview' => ['Interview', '#8b5cf6'],
                            'offered' => ['Offered', '#06b6d4'],
                            'hired' => ['Hired', '#16a34a'],
                            'internship_offered' => ['Internship Offer', '#0f766e'],
                            'rejected' => ['Rejected', '#ef4444'],
                        ];
                    @endphp
                    @foreach ($map as $key => [$label, $color])
                        <div class="kk-status-row">
                            <span class="kk-legend__left"><i style="background:{{ $color }}"></i>{{ $label }}</span>
                            <strong>{{ $statusCounts[$key] ?? 0 }}</strong>
                            <span class="kk-muted">{{ number_format((($statusCounts[$key] ?? 0) / $total) * 100, 1) }}%</span>
                        </div>
                    @endforeach
                </div>
                <div class="kk-chart-box kk-chart-box--sm"><canvas id="statusDonut"></canvas></div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!window.Chart) return;
  const spark = (id, color, data) => {
    const ctx = document.getElementById(id);
    if (!ctx) return;
    new Chart(ctx, {
      type: 'line',
      data: { labels: data.map((_, i) => i), datasets: [{ data, borderColor: color, backgroundColor: color + '22', fill: true, tension: .4, pointRadius: 0, borderWidth: 2 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: { enabled: false } }, scales: { x: { display: false }, y: { display: false } } }
    });
  };
  spark('sparkTotal', '#2563eb', [12,18,14,22,19,28,24,30,26,34]);
  spark('sparkNew', '#16a34a', [8,10,9,14,12,16,15,18,17,20]);
  spark('sparkOpen', '#f59e0b', [5,5,6,6,7,7,8,8,9,9]);
  spark('sparkHired', '#8b5cf6', [2,3,3,4,5,5,6,7,7,8]);

  const overviewLabels = @json($overviewLabels);
  const overviewData = @json($overviewData);
  new Chart(document.getElementById('overviewChart'), {
    type: 'line',
    data: {
      labels: overviewLabels,
      datasets: [{
        label: 'Applications',
        data: overviewData,
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37,99,235,.12)',
        fill: true,
        tension: .35,
        pointRadius: 0,
        pointHoverRadius: 5,
        borderWidth: 2.5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8', maxTicksLimit: 8 } },
        y: { grid: { color: '#eef2f7' }, ticks: { color: '#94a3b8' }, beginAtZero: true }
      }
    }
  });

  const sourceLabels = @json(array_keys($sources));
  const sourceData = @json(array_values($sources));
  new Chart(document.getElementById('sourceChart'), {
    type: 'doughnut',
    data: {
      labels: sourceLabels,
      datasets: [{ data: sourceData, backgroundColor: ['#2563eb','#22c55e','#f59e0b','#8b5cf6','#94a3b8'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '72%',
      plugins: {
        legend: { display: false },
        tooltip: { callbacks: { label: (c) => ` ${c.label}: ${c.raw}` } }
      }
    }
  });

  const statusLabels = ['New','Review','Shortlisted','Interview','Offered','Hired','Rejected'];
  const statusData = @json(array_values($statusCounts));
  new Chart(document.getElementById('statusChart'), {
    type: 'bar',
    data: {
      labels: statusLabels,
      datasets: [
        { type: 'bar', label: 'Applications', data: statusData, backgroundColor: '#2563eb', borderRadius: 8, barThickness: 28 },
        { type: 'line', label: 'Hired trend', data: statusData.map((v,i)=> Math.max(0, Math.round(v * (i/6)))), borderColor: '#22c55e', tension: .35, pointRadius: 3 }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
        y: { grid: { color: '#eef2f7' }, ticks: { color: '#94a3b8' }, beginAtZero: true }
      }
    }
  });

  new Chart(document.getElementById('statusDonut'), {
    type: 'doughnut',
    data: {
      labels: statusLabels,
      datasets: [{ data: statusData, backgroundColor: ['#2563eb','#f59e0b','#22c55e','#8b5cf6','#06b6d4','#16a34a','#ef4444'], borderWidth: 0 }]
    },
    options: { responsive: true, maintainAspectRatio: false, cutout: '68%', plugins: { legend: { display: false } } }
  });
});
</script>
@endpush
