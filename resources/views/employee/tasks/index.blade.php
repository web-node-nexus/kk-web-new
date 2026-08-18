@extends('layouts.employee')
@section('title', 'My Tasks')
@section('heading', 'My Tasks')
@section('subheading', 'Tasks assigned to you by admin. Open one to reply and update status.')

@section('content')
<div class="ep-card">
    <div class="ep-card__h"><h3>Assigned to you</h3></div>
    <div class="ep-card__b ep-table-wrap">
        <table class="ep-table">
            <thead>
                <tr>
                    <th>Task</th>
                    <th>Priority</th>
                    <th>Sent</th>
                    <th>Due</th>
                    <th>Your status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($assignments as $row)
                    @php $task = $row->task; $st = strtolower((string) $row->status); @endphp
                    <tr>
                        <td>
                            <strong>{{ $task?->title ?: 'Task' }}</strong>
                            <div class="ep-muted" style="font-size:12px;margin-top:2px">{{ \Illuminate\Support\Str::limit(strip_tags((string) ($task?->description ?? '')), 70) }}</div>
                        </td>
                        <td>{{ ucfirst($task?->priority ?: 'normal') }}</td>
                        <td>{{ $task ? \App\Support\AppTime::formatShort($task->created_at) : '—' }}</td>
                        <td>{{ $task?->dueLabel() }}</td>
                        <td>
                            <span class="ep-pill {{ $st === 'completed' ? 'ep-pill--ok' : ($st === 'in_progress' ? 'ep-pill--info' : 'ep-pill--warn') }}">{{ str_replace('_', ' ', $row->status) }}</span>
                        </td>
                        <td><a class="ep-btn ep-btn-ghost" href="{{ route('employee.tasks.show', $task) }}">Open</a></td>
                    </tr>
                @empty
                    <tr class="ep-empty-row"><td colspan="6"><div class="ep-empty"><strong>No tasks yet</strong>Jab admin aapko task dega, yahan dikhega.</div></td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $assignments->links() }}</div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const tbody = document.querySelector('.ep-table tbody');
  if (!tbody) return;
  let lastId = {{ (int) $assignments->max('id') }};
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const add = (item) => {
    if (!item || Number(item.id) <= lastId) return;
    lastId = Math.max(lastId, Number(item.id));
    document.querySelector('.ep-empty-row')?.remove();
    const tr = document.createElement('tr');
    tr.innerHTML = `<td><strong>${esc(item.title)}</strong><div class="ep-muted" style="font-size:12px;margin-top:2px">${esc(item.excerpt)}</div></td><td>${esc(item.priority)}</td><td>${esc(item.sent)}</td><td>${esc(item.due)}</td><td><span class="ep-pill ep-pill--warn">${esc(item.status)}</span></td><td><a class="ep-btn ep-btn-ghost" href="${esc(item.url)}">Open</a></td>`;
    tbody.prepend(tr);
  };
  const load = async () => {
    try {
      const res = await fetch(@json(route('employee.tasks.feed')) + '?after=' + lastId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      (data.items || []).reverse().forEach(add);
    } catch (e) {}
  };
  setInterval(load, 1500);
})();
</script>
@endpush
