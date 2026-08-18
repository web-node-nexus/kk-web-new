@extends('layouts.admin')
@section('title', 'Analytics | Admin')
@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Analytics</h1>
        <p>Conversion funnel and hiring performance metrics.</p>
    </div>
</div>
<div class="kk-stats">
    @foreach ($conversion as $label => $value)
        <article class="kk-card kk-stat">
            <p class="kk-stat__label">{{ str_replace('_', ' → ', ucwords(str_replace('_', ' ', $label))) }}</p>
            <div class="kk-stat__value">{{ $value }}</div>
            <div class="kk-stat__trend"><span>Funnel conversion</span></div>
        </article>
    @endforeach
</div>
<section class="kk-card">
    <div class="kk-card__head"><h2>Applications by status</h2></div>
    <div class="kk-card__body">
        <div class="kk-chart-box"><canvas id="analyticsChart"></canvas></div>
    </div>
</section>
@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  if (!window.Chart) return;
  const data = @json($byStatus);
  new Chart(document.getElementById('analyticsChart'), {
    type: 'bar',
    data: {
      labels: Object.keys(data).map(s => s.charAt(0).toUpperCase() + s.slice(1)),
      datasets: [{ data: Object.values(data), backgroundColor: '#2563eb', borderRadius: 8, barThickness: 36 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: { x: { grid: { display: false } }, y: { beginAtZero: true, grid: { color: '#eef2f7' } } }
    }
  });
});
</script>
@endpush
