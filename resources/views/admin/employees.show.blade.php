@extends('layouts.admin')

@section('title', $item->name.' | Employee')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $item->name }}</h1>
        <p>Full employee profile, pay, attendance and task timing.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.module.edit', ['employees', $item->id]) }}" class="kk-btn kk-btn-primary">Edit</a>
        <a href="{{ route('admin.module.index', 'employees') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body" style="display:flex;gap:22px;flex-wrap:wrap;align-items:center">
        @if ($item->photoUrl())
            <img src="{{ $item->photoUrl() }}" alt="" style="width:88px;height:88px;border-radius:18px;object-fit:cover;border:1px solid #e2e8f0">
        @else
            <div class="kk-profile__avatar" style="width:88px;height:88px;font-size:26px;border-radius:18px">{{ strtoupper(substr($item->name, 0, 1)) }}</div>
        @endif
        <div>
            <strong style="display:block;font-size:20px">{{ $item->name }}</strong>
            <span class="kk-muted">{{ $item->role_title ?: 'Team member' }} · {{ $item->employee_code ?: 'No code' }}</span>
            <div style="margin-top:8px;display:flex;gap:8px;flex-wrap:wrap">
                <span class="kk-pill kk-pill--{{ $item->status === 'active' ? 'hired' : 'review' }}">{{ $item->status }}</span>
                @if ($item->employment_type)<span class="kk-pill">{{ $item->employment_type }}</span>@endif
                @if ($item->job_type)<span class="kk-pill">{{ $item->job_type }}</span>@endif
            </div>
        </div>
    </div>
</section>

<div class="kk-stats" style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:16px">
    <section class="kk-card"><div class="kk-card__body">
        <p class="kk-muted" style="margin:0 0 4px;font-size:12px">Delayed tasks</p>
        <strong style="font-size:28px;color:#b91c1c">{{ $delayedTasks }}</strong>
    </div></section>
    <section class="kk-card"><div class="kk-card__body">
        <p class="kk-muted" style="margin:0 0 4px;font-size:12px">On time</p>
        <strong style="font-size:28px;color:#047857">{{ $onTimeTasks }}</strong>
    </div></section>
    <section class="kk-card"><div class="kk-card__body">
        <p class="kk-muted" style="margin:0 0 4px;font-size:12px">Total assigned</p>
        <strong style="font-size:28px">{{ $totalTasks }}</strong>
    </div></section>
</div>

<div style="display:grid;grid-template-columns:1.1fr .9fr;gap:16px" class="kk-emp-detail-grid">
    <section class="kk-card">
        <div class="kk-card__body">
            <h3 style="margin:0 0 14px">All details</h3>
            <dl class="kk-kv">
                <dt>Name</dt><dd>{{ $item->name }}</dd>
                <dt>Email</dt><dd>{{ $item->email }}</dd>
                <dt>Phone</dt><dd>{{ $item->phone ?: '—' }}</dd>
                <dt>Employee code</dt><dd>{{ $item->employee_code ?: '—' }}</dd>
                <dt>Role title</dt><dd>{{ $item->role_title ?: '—' }}</dd>
                <dt>Emp type</dt><dd>{{ $item->employment_type ?: '—' }}</dd>
                <dt>Job type</dt><dd>{{ $item->job_type ?: '—' }}</dd>
                <dt>Department</dt><dd>{{ $item->department->name ?? '—' }}</dd>
                <dt>Join date</dt><dd>{{ optional($item->join_date)->format('d M Y') ?: '—' }}</dd>
                <dt>Salary</dt><dd>{{ $item->salary ? '₹'.number_format((float) $item->salary, 0) : '—' }}</dd>
                <dt>Status</dt><dd>{{ $item->status }}</dd>
            </dl>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__body">
            <h3 style="margin:0 0 14px">Pay graph</h3>
            <canvas id="payChart" height="180"></canvas>
        </div>
    </section>
</div>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h3 style="margin:0 0 14px">Attendance graph</h3>
        <canvas id="attChart" height="120"></canvas>
    </div>
</section>

<section class="kk-card" style="margin-top:16px">
    <div class="kk-card__body">
        <h3 style="margin:0 0 14px">Recent attendance</h3>
        <div class="kk-table-wrap">
            <table class="kk-table">
                <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                <tbody>
                @forelse ($attendance as $row)
                    <tr>
                        <td>{{ optional($row->date)->format('d M Y') }}</td>
                        <td>{{ $row->check_in ? \Illuminate\Support\Carbon::parse($row->check_in)->format('h:i A') : '—' }}</td>
                        <td>{{ $row->check_out ? \Illuminate\Support\Carbon::parse($row->check_out)->format('h:i A') : '—' }}</td>
                        <td>{{ $row->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4">No attendance yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const payLabels = @json($payLabels);
  const payValues = @json($payValues);
  const monthLabels = @json($monthLabels);
  const presentCounts = @json($presentCounts);
  const lateCounts = @json($lateCounts);
  if (!window.Chart) return;
  const payEl = document.getElementById('payChart');
  if (payEl) {
    new Chart(payEl, {
      type: 'line',
      data: { labels: payLabels.length ? payLabels : ['No data'], datasets: [{ label: 'Net pay', data: payValues.length ? payValues : [0], borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,.12)', tension: .35, fill: true }] },
      options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
  }
  const attEl = document.getElementById('attChart');
  if (attEl) {
    new Chart(attEl, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: [
          { label: 'Present', data: presentCounts, backgroundColor: '#0d9488' },
          { label: 'Late', data: lateCounts, backgroundColor: '#f59e0b' }
        ]
      },
      options: { plugins: { legend: { position: 'bottom' } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });
  }
})();
</script>
@endpush
