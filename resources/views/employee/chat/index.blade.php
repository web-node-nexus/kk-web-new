@extends('layouts.employee')
@section('title', 'Live Chat')
@section('heading', 'Live chat')
@section('subheading', 'Chat live with admin. Messages appear here instantly.')

@section('content')
<div class="ep-card ep-chat-card">
    <div class="ep-card__h">
        <h3>Admin</h3>
        <span class="ep-pill ep-pill--ok">Live</span>
    </div>
    <div class="ep-chat">
        <div class="ep-chat__msgs" id="chatMsgs"></div>
        <form class="ep-chat__composer" id="chatForm">
            @csrf
            <input type="text" id="chatInput" maxlength="4000" placeholder="Type a message…" autocomplete="off" required>
            <button class="ep-btn ep-btn-primary" type="submit">Send</button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const msgsEl = document.getElementById('chatMsgs');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const pollUrl = @json(route('employee.chat.messages'));
  const sendUrl = @json(route('employee.chat.send'));
  let lastId = 0;
  let sending = false;

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const append = (m) => {
    if (!m || Number(m.id) <= lastId) return;
    lastId = Math.max(lastId, Number(m.id));
    const mine = m.sender_type === 'employee';
    const div = document.createElement('div');
    div.className = 'ep-bubble ' + (mine ? 'is-me' : 'is-them');
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

  load();
  setInterval(load, 1000);
})();
</script>
@endpush
