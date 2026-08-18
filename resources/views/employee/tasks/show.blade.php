@extends('layouts.employee')
@section('title', $task->title)
@section('heading', $task->title)
@section('subheading', 'Reply to admin and update your progress on this task.')

@section('content')
<div class="ep-grid-2">
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Task details</h3></div>
        <div class="ep-card__b">
            <p class="ep-muted" style="margin-top:0">
                {{ ucfirst($task->priority) }} priority
                · Sent {{ \App\Support\AppTime::format($task->created_at) }}
                @if ($task->due_at || $task->due_date) · Due {{ $task->dueLabel() }} @endif
                · From {{ $task->creator?->name ?: 'Admin' }}
            </p>
            <div class="ep-rich">{!! $task->description ? \App\Support\SafeHtml::clean($task->description) : 'No extra details.' !!}</div>
            @include('partials.task-media', ['item' => $task])

            <form method="POST" action="{{ route('employee.tasks.status', $task) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;margin-top:16px">
                @csrf
                <div class="ep-field" style="margin:0;min-width:180px">
                    <label for="status">My status</label>
                    <select id="status" name="status">
                        @foreach (['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'] as $val => $label)
                            <option value="{{ $val }}" @selected($assignment->status === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="ep-btn ep-btn-navy" type="submit">Update</button>
            </form>
        </div>
    </div>

    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Replies</h3></div>
        <div class="ep-card__b">
            <div class="ep-thread">
                @forelse ($task->replies as $reply)
                    <article class="ep-thread__item {{ $reply->sender_type === 'employee' ? 'is-me' : '' }}">
                        <div class="ep-thread__meta">
                            <strong>{{ $reply->senderName() }}</strong>
                            <em>{{ \App\Support\AppTime::formatShort($reply->created_at) }}</em>
                        </div>
                        @if (filled($reply->message))
                            <p>{{ $reply->message }}</p>
                        @endif
                        @include('partials.task-media', ['item' => $reply])
                    </article>
                @empty
                    <div class="ep-empty">No replies yet. Write the first update below.</div>
                @endforelse
            </div>
            <form method="POST" action="{{ route('employee.tasks.reply', $task) }}" style="margin-top:16px" enctype="multipart/form-data">
                @csrf
                <div class="ep-field">
                    <label for="message">Reply / revert to admin</label>
                    <textarea id="message" name="message" rows="3" placeholder="Write your update…">{{ old('message') }}</textarea>
                </div>
                @include('employee.tasks._media-fields')
                <button class="ep-btn ep-btn-primary" type="submit">Send reply</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const thread = document.querySelector('.ep-thread');
  if (!thread) return;
  let lastId = {{ (int) $task->replies->max('id') }};
  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const add = (r) => {
    if (!r || Number(r.id) <= lastId) return;
    lastId = Number(r.id);
    thread.querySelector('.ep-empty')?.remove();
    const art = document.createElement('article');
    art.className = 'ep-thread__item ' + (r.sender_type === 'employee' ? 'is-me' : '');
    art.innerHTML = `<div class="ep-thread__meta"><strong>${esc(r.sender)}</strong><em>${esc(r.time)}</em></div><p>${esc(r.message || '')}</p>`;
    thread.appendChild(art);
  };
  const load = async () => {
    try {
      const res = await fetch(@json(route('employee.tasks.replies', $task)) + '?after=' + lastId, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
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
