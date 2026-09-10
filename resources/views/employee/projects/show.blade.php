@extends('layouts.employee')
@section('title', $project->name.' | Project Group')
@section('heading', $project->name)
@section('subheading', 'Group chat — type @ to mention a teammate.')

@section('content')
<div class="ep-group-layout">
    <aside class="ep-card ep-group-members">
        <div class="ep-card__h"><h3>Members</h3></div>
        <div class="ep-card__b" style="display:grid;gap:10px">
            @foreach ($members as $m)
                <div class="ep-group-member">
                    @if (!empty($m['photo']))
                        <img src="{{ $m['photo'] }}" alt="">
                    @else
                        <span class="ep-group-member__ini">{{ strtoupper(substr($m['name'], 0, 1)) }}</span>
                    @endif
                    <div>
                        <strong>{{ $m['name'] }}</strong>
                        <em>{{ $m['role'] }}@if(!empty($m['code'])) · {{ $m['code'] }}@endif</em>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="padding:0 16px 16px">
            <a href="{{ route('employee.projects.index') }}" class="ep-btn" style="width:100%;justify-content:center">All groups</a>
        </div>
    </aside>

    <section class="ep-card ep-chat-card">
        <div class="ep-card__h">
            <div>
                <h3 style="margin:0">{{ $project->name }}</h3>
                <p class="ep-muted" style="margin:4px 0 0;font-size:12px">{{ $project->client_name }} · Use @name to mention</p>
            </div>
            <span class="ep-pill ep-pill--ok">Group</span>
        </div>
        <div class="ep-chat">
            <div class="ep-chat__msgs" id="chatMsgs"></div>
            <div class="ep-mention-box" id="mentionBox" hidden></div>
            <form class="ep-chat__composer" id="chatForm">
                @csrf
                <input type="text" id="chatInput" maxlength="4000" placeholder="Message the group… type @ to mention" autocomplete="off" required>
                <button class="ep-btn ep-btn-primary" type="submit">Send</button>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(() => {
  const msgsEl = document.getElementById('chatMsgs');
  const form = document.getElementById('chatForm');
  const input = document.getElementById('chatInput');
  const mentionBox = document.getElementById('mentionBox');
  const token = document.querySelector('meta[name="csrf-token"]')?.content;
  const pollUrl = @json(route('employee.projects.messages', $project->id));
  const sendUrl = @json(route('employee.projects.send', $project->id));
  const meId = {{ (int) $employee->id }};
  const members = @json($members);
  let lastId = 0;
  let sending = false;
  let mentionActive = false;
  let mentionFilter = '';
  let mentionIndex = 0;

  const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

  const highlight = (text) => {
    let html = esc(text);
    members.forEach((m) => {
      const name = String(m.name || '');
      if (!name) return;
      const re = new RegExp('(@' + name.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')(?=\\s|[.,!?;:]|$)', 'gi');
      html = html.replace(re, '<span class="ep-mention">$1</span>');
    });
    return html;
  };

  const append = (m) => {
    if (!m || Number(m.id) <= lastId) return;
    lastId = Math.max(lastId, Number(m.id));
    const mine = Number(m.sender_id) === meId;
    const div = document.createElement('div');
    div.className = 'ep-bubble ' + (mine ? 'is-me' : 'is-them');
    div.innerHTML = `<strong>${esc(m.sender)}</strong><p>${highlight(m.message)}</p><em>${esc(m.time)}</em>`;
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

  const filteredMembers = () => {
    const q = mentionFilter.toLowerCase();
    return members.filter((m) => Number(m.id) !== meId).filter((m) => {
      if (!q) return true;
      return String(m.name).toLowerCase().includes(q) || String(m.code || '').toLowerCase().includes(q);
    }).slice(0, 6);
  };

  const renderMentions = () => {
    const list = filteredMembers();
    if (!mentionActive || !list.length) {
      mentionBox.hidden = true;
      return;
    }
    mentionIndex = Math.min(mentionIndex, list.length - 1);
    mentionBox.hidden = false;
    mentionBox.innerHTML = list.map((m, i) => `
      <button type="button" class="ep-mention-item ${i === mentionIndex ? 'is-active' : ''}" data-name=${JSON.stringify(m.name)}>
        <strong>${esc(m.name)}</strong>
        <span>${esc(m.role || '')}</span>
      </button>
    `).join('');
  };

  const insertMention = (name) => {
    const val = input.value;
    const caret = input.selectionStart || val.length;
    const before = val.slice(0, caret);
    const after = val.slice(caret);
    const at = before.lastIndexOf('@');
    if (at < 0) return;
    input.value = before.slice(0, at) + '@' + name + ' ' + after;
    mentionActive = false;
    mentionBox.hidden = true;
    input.focus();
    const pos = (before.slice(0, at) + '@' + name + ' ').length;
    input.setSelectionRange(pos, pos);
  };

  input?.addEventListener('input', () => {
    const caret = input.selectionStart || 0;
    const before = input.value.slice(0, caret);
    const match = before.match(/@([^\s@]*)$/);
    if (match) {
      mentionActive = true;
      mentionFilter = match[1] || '';
      mentionIndex = 0;
      renderMentions();
    } else {
      mentionActive = false;
      mentionBox.hidden = true;
    }
  });

  input?.addEventListener('keydown', (e) => {
    if (!mentionActive || mentionBox.hidden) return;
    const list = filteredMembers();
    if (!list.length) return;
    if (e.key === 'ArrowDown') {
      e.preventDefault();
      mentionIndex = (mentionIndex + 1) % list.length;
      renderMentions();
    } else if (e.key === 'ArrowUp') {
      e.preventDefault();
      mentionIndex = (mentionIndex - 1 + list.length) % list.length;
      renderMentions();
    } else if (e.key === 'Enter' || e.key === 'Tab') {
      e.preventDefault();
      insertMention(list[mentionIndex].name);
    } else if (e.key === 'Escape') {
      mentionActive = false;
      mentionBox.hidden = true;
    }
  });

  mentionBox?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-name]');
    if (!btn) return;
    insertMention(btn.getAttribute('data-name'));
  });

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
      mentionActive = false;
      mentionBox.hidden = true;
    } catch (err) {}
    sending = false;
    input.focus();
  });

  load();
  setInterval(load, 1500);
})();
</script>
@endpush
