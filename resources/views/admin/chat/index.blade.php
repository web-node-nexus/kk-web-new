@extends('layouts.admin')

@section('title', 'Live Chat | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Live chat</h1>
        <p>Chat live with any employee. Messages refresh every few seconds.</p>
    </div>
</div>

<div class="kk-chat">
    <aside class="kk-chat__list">
        <input type="search" id="chatEmpSearch" placeholder="Search employee…" class="kk-chat__search">
        <div id="chatEmpList">
            @forelse ($employees as $emp)
                @php
                    $last = $lastMessages[$emp->id] ?? null;
                    $unreadCount = (int) ($unread[$emp->id] ?? 0);
                    $isActive = $active && (int) $active->id === (int) $emp->id;
                @endphp
                <a href="{{ route('admin.chat.index', ['employee' => $emp->id]) }}"
                   class="kk-chat__person {{ $isActive ? 'is-active' : '' }}"
                   data-search="{{ strtolower($emp->name.' '.$emp->email.' '.$emp->role_title) }}">
                    <strong>{{ $emp->name }}</strong>
                    <span>{{ $emp->role_title ?: $emp->email }}</span>
                    @if ($last)
                        <em>{{ \Illuminate\Support\Str::limit($last->message, 42) }}</em>
                    @endif
                    @if ($unreadCount > 0)
                        <b>{{ $unreadCount }}</b>
                    @endif
                </a>
            @empty
                <p class="kk-muted" style="padding:12px">No active employees.</p>
            @endforelse
        </div>
    </aside>

    <section class="kk-chat__pane">
        @if ($active)
            <header class="kk-chat__head">
                <div>
                    <strong>{{ $active->name }}</strong>
                    <span>{{ $active->role_title ?: $active->email }}</span>
                </div>
                <span class="kk-pill kk-pill--hired" id="chatLive">Live</span>
            </header>
            <div class="kk-chat__msgs" id="chatMsgs"></div>
            <form class="kk-chat__composer" id="chatForm">
                @csrf
                <input type="text" id="chatInput" maxlength="4000" placeholder="Type a message…" autocomplete="off" required>
                <button class="kk-btn kk-btn-primary" type="submit">Send</button>
            </form>
        @else
            <div class="kk-empty" style="margin:auto">Select an employee to start chatting.</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
@if ($active)
<script>
(() => {
  const msgsEl = document.getElementById('chatMsgs');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const search = document.getElementById('chatEmpSearch');
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const pollUrl = @json(route('admin.chat.messages', $active->id));
  const sendUrl = @json(route('admin.chat.send', $active->id));
  let lastId = 0;
  let sending = false;

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const append = (m) => {
    if (!m || Number(m.id) <= lastId) return;
    lastId = Math.max(lastId, Number(m.id));
    const mine = m.sender_type === 'admin';
    const div = document.createElement('div');
    div.className = 'kk-bubble ' + (mine ? 'is-me' : 'is-them');
    div.innerHTML = `<strong>${esc(m.sender)}</strong><p>${esc(m.message)}</p><em>${esc(m.time)}</em>`;
    msgsEl.appendChild(div);
    msgsEl.scrollTop = msgsEl.scrollHeight;
  };

  const load = async () => {
    try {
      const url = pollUrl + (lastId ? ('?after=' + lastId) : '');
      const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      const data = await res.json();
      (data.messages || []).forEach(append);
    } catch (e) {}
  };

  form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = (input.value || '').trim();
    if (!text || sending) return;
    sending = true;
    try {
      const res = await fetch(sendUrl, {
        method: 'POST',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': token,
          'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ message: text }),
      });
      const data = await res.json();
      if (data.message) append(data.message);
      input.value = '';
    } catch (err) {}
    sending = false;
    input.focus();
  });

  search?.addEventListener('input', () => {
    const q = (search.value || '').trim().toLowerCase();
    document.querySelectorAll('.kk-chat__person').forEach((el) => {
      const hay = el.getAttribute('data-search') || '';
      el.style.display = !q || hay.includes(q) ? '' : 'none';
    });
  });

  load();
  setInterval(load, 1000);
})();
</script>
@endif
@endpush
