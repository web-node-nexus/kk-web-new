@extends('layouts.admin')

@section('title', $item->title.' | Tasks')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $item->title }}</h1>
        <p>
            Assigned to {{ $item->audienceLabel() }}
            · {{ ucfirst($item->priority) }} priority
            · Sent {{ \App\Support\AppTime::format($item->created_at) }}
            @if ($item->due_at || $item->due_date) · Due {{ $item->dueLabel() }} @endif
            · By {{ $item->creator?->name ?: 'Admin' }}
        </p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.tasks.index') }}" class="kk-btn kk-btn-secondary">Back</a>
        <form method="POST" action="{{ route('admin.tasks.status', $item->id) }}" style="display:flex;gap:8px;align-items:center">
            @csrf
            <select name="status" class="kk-select-inline">
                @foreach (['open' => 'Open', 'in_progress' => 'In progress', 'done' => 'Done'] as $val => $label)
                    <option value="{{ $val }}" @selected($item->status === $val)>{{ $label }}</option>
                @endforeach
            </select>
            <button class="kk-btn kk-btn-secondary" type="submit">Update status</button>
        </form>
    </div>
</div>

<div class="kk-grid-2" style="display:grid;grid-template-columns:1.2fr .8fr;gap:18px;align-items:start">
    <section class="kk-card" style="margin:0">
        <div class="kk-card__body">
            <h3 style="margin:0 0 10px">Task details</h3>
            <div class="kk-rich">{!! $item->description ? \App\Support\SafeHtml::clean($item->description) : 'No description.' !!}</div>
            @include('partials.task-media', ['item' => $item])

            <h3 style="margin:28px 0 12px">Replies</h3>
            <div class="kk-thread">
                @forelse ($item->replies as $reply)
                    <article class="kk-thread__item {{ $reply->sender_type === 'admin' ? 'is-admin' : 'is-emp' }}">
                        <div class="kk-thread__meta">
                            <strong>{{ $reply->senderName() }}</strong>
                            <span>{{ $reply->sender_type === 'admin' ? 'Admin' : 'Employee' }}</span>
                            <em>{{ \App\Support\AppTime::formatShort($reply->created_at) }}</em>
                        </div>
                        @if (filled($reply->message))
                            <p>{{ $reply->message }}</p>
                        @endif
                        @include('partials.task-media', ['item' => $reply])
                    </article>
                @empty
                    <p class="kk-muted">No replies yet. Employees can reply from their panel.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('admin.tasks.reply', $item->id) }}" style="margin-top:16px" enctype="multipart/form-data">
                @csrf
                <div class="kk-field">
                    <label>Reply to employees</label>
                    <textarea name="message" rows="3" placeholder="Write a reply…">{{ old('message') }}</textarea>
                </div>
                @include('admin.tasks._media-fields')
                <div class="kk-form-actions" style="margin-top:10px">
                    <button class="kk-btn kk-btn-primary" type="submit">Send reply</button>
                </div>
            </form>
        </div>
    </section>

    <section class="kk-card" style="margin:0">
        <div class="kk-card__body">
            <h3 style="margin:0 0 12px">Assignees</h3>
            <div class="kk-table-wrap">
                <table class="kk-table">
                    <thead>
                        <tr><th>Employee</th><th>Status</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($item->assignmentRows as $row)
                            <tr>
                                <td>
                                    <strong>{{ $row->employee?->name ?: 'Employee' }}</strong>
                                    <div class="kk-muted" style="font-size:12px">{{ $row->employee?->email }}</div>
                                </td>
                                <td><span class="kk-pill kk-pill--{{ $row->status === 'completed' ? 'hired' : ($row->status === 'in_progress' ? 'interview' : 'pending') }}">{{ str_replace('_', ' ', $row->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="kk-empty">No assignees.</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const thread = document.querySelector('.kk-thread');
  if (!thread) return;
  let lastId = {{ (int) $item->replies->max('id') }};
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const add = (r) => {
    if (!r || Number(r.id) <= lastId) return;
    lastId = Number(r.id);
    const art = document.createElement('article');
    art.className = 'kk-thread__item ' + (r.sender_type === 'admin' ? 'is-admin' : 'is-emp');
    art.innerHTML = `<div class="kk-thread__meta"><strong>${esc(r.sender)}</strong><em>${esc(r.time)}</em></div><p>${esc(r.message || '')}</p>`;
    thread.appendChild(art);
  };
  const load = async () => {
    try {
      const res = await fetch(@json(route('admin.tasks.replies', $item->id)) + '?after=' + lastId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      (data.replies || []).forEach(add);
    } catch (e) {}
  };
  document.addEventListener('kk:reply', (e) => add(e.detail));
  load();
  setInterval(load, 1000);
})();
</script>
@endpush
