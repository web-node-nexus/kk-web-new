@extends('layouts.admin')

@section('title', $mod['title'].' | KK Digital Admin')

@php
    $val = function ($item, $key) {
        if (!$item) return null;
        if (!str_contains($key, '.')) return $item->{$key} ?? null;
        $parts = explode('.', $key);
        $cur = $item;
        foreach ($parts as $p) {
            if (!$cur) return null;
            $cur = is_array($cur) ? ($cur[$p] ?? null) : ($cur->{$p} ?? null);
        }
        return $cur;
    };
    $isWorkflow = !empty($mod['workflow']);
    $isHrNotify = !empty($mod['hr_notify']);
    $hasChecks = $isWorkflow || $isHrNotify;
@endphp

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $mod['title'] }}</h1>
        <p>{{ $mod['subtitle'] ?? '' }}</p>
    </div>
    <div class="kk-pagehead__actions">
        @unless ($isWorkflow)
            <a href="{{ route('admin.module.create', $mod['key']) }}" class="kk-btn kk-btn-primary">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                Add New
            </a>
        @endunless
    </div>
</div>

@if ($isWorkflow)
<div class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body" style="padding:14px 18px">
        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.55">
            <strong style="color:#0f172a">Flow:</strong>
            New → Reviewed → Shortlisted → Interview → Offered → <strong>Approve / Hire</strong>
            &nbsp;·&nbsp; kisi bhi step pe <strong>Reject</strong> kar sakte ho.
        </p>
    </div>
</div>
@endif

@if ($isHrNotify)
<div class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body" style="padding:14px 18px">
        <p style="margin:0;font-size:13px;color:#64748b;line-height:1.55">
            <strong style="color:#0f172a">HR assign:</strong>
            Neeche se HR <strong>search and select</strong> (name / email / role), tick interviews, then <strong>Send selected to HR</strong> — or use <strong>Send to HR</strong> on a row.
            The interview appears in the selected HR employee panel (email must match).
        </p>
    </div>
</div>
@endif

<div class="kk-toolbar">
    <div class="kk-filters">
        @if (!empty($counts))
            <a href="{{ route('admin.module.index', [$mod['key'], 'q' => $filters['q']]) }}" class="{{ $filters['status'] === 'all' ? 'active' : '' }}">All ({{ $counts['all'] ?? 0 }})</a>
            @foreach (($mod['statuses'] ?? []) as $st)
                <a href="{{ route('admin.module.index', [$mod['key'], 'status' => $st, 'q' => $filters['q']]) }}" class="{{ $filters['status'] === $st ? 'active' : '' }}">{{ $st === 'internship_offered' ? 'Internship Offer' : ucfirst($st === 'hired' || $st === 'approved' ? 'Approved' : $st) }} ({{ $counts[$st] ?? 0 }})</a>
            @endforeach
        @endif
    </div>
    <form class="kk-searchbar" method="GET" action="{{ route('admin.module.index', $mod['key']) }}">
        @if ($filters['status'] !== 'all')
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
        @endif
        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Search {{ strtolower($mod['title']) }}...">
        <button class="kk-btn kk-btn-secondary" type="submit">Search</button>
    </form>
</div>

@if ($isWorkflow)
<form id="bulkForm" method="POST" action="{{ route('admin.applications.bulk-status') }}" class="kk-toolbar" style="margin-top:-4px">
    @csrf
    <div id="bulkIds"></div>
    <div class="kk-decision" style="flex-wrap:wrap">
        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" name="status" value="reviewed">Mark Reviewed</button>
        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" name="status" value="shortlisted" style="border-color:#bbf7d0;color:#15803d;background:#f0fdf4">Shortlist</button>
        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" name="status" value="interview" style="border-color:#ddd6fe;color:#6d28d9;background:#f5f3ff">Interview</button>
        <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit" name="status" value="hired">✓ Approve / Hire</button>
        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" name="status" value="internship_offered" style="border-color:#99f6e4;color:#0f766e;background:#f0fdfa" onclick="return confirm('Reject full-time job and send internship offer emails to selected candidates?')">Offer Internship</button>
        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit" name="status" value="rejected" onclick="return confirm('Reject selected applications?')">Reject</button>
    </div>
    <span class="kk-muted" id="selectedCount">0 selected</span>
</form>
@endif

@if ($isHrNotify)
<form id="hrBulkForm" method="POST" action="{{ route('admin.interviews.bulk-notify-hr') }}" class="kk-toolbar" style="margin-top:-4px;flex-wrap:wrap;gap:12px;align-items:flex-end">
    @csrf
    <div id="hrBulkIds"></div>
    <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;flex:1">
        <div style="min-width:min(100%,320px);flex:1.2;position:relative">
            <label style="display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px">Select HR (search & choose)</label>
            @include('admin.partials.hr-picker', [
                'inputName' => 'hr_email',
                'selectedEmail' => old('hr_email'),
                'required' => true,
                'pickerId' => 'hrPickerBulk',
            ])
        </div>
        <div style="flex:1;min-width:220px">
            <label style="display:block;font-size:11px;font-weight:700;color:#64748b;margin-bottom:4px">Meeting link (optional)</label>
            <input type="url" name="meeting_link" value="{{ old('meeting_link') }}" placeholder="https://meet.google.com/..." style="width:100%;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px">
        </div>
        <button class="kk-btn kk-btn-primary kk-btn-sm" type="submit">Send selected to HR</button>
        <span class="kk-muted" id="hrSelectedCount">0 selected</span>
    </div>
</form>
@endif

<section class="kk-card">
    <div class="kk-table-wrap">
        <table class="kk-table">
            <thead>
                <tr>
                    @if ($hasChecks)
                        <th style="width:36px"><input type="checkbox" id="checkAll" aria-label="Select all"></th>
                    @endif
                    @foreach ($mod['columns'] as $col)
                        <th>{{ $col['label'] }}</th>
                    @endforeach
                    <th style="text-align:right">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        @if ($hasChecks)
                            <td><input type="checkbox" class="row-check" value="{{ $item->id }}"></td>
                        @endif
                        @foreach ($mod['columns'] as $col)
                            @php $raw = $val($item, $col['key']); @endphp
                            <td>
                                @if (($col['type'] ?? '') === 'employee')
                                    <div style="display:flex;align-items:center;gap:10px">
                                        @if (method_exists($item, 'photoUrl') && $item->photoUrl())
                                            <img src="{{ $item->photoUrl() }}" alt="" style="width:36px;height:36px;border-radius:10px;object-fit:cover;border:1px solid #e2e8f0;flex-shrink:0">
                                        @else
                                            @php
                                                $ini = collect(explode(' ', (string) $raw))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
                                            @endphp
                                            <span style="width:36px;height:36px;border-radius:10px;background:#0f766e;color:#fff;display:inline-flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">{{ $ini ?: 'E' }}</span>
                                        @endif
                                        <span class="kk-row-title">{{ $raw ?? '—' }}</span>
                                    </div>
                                @elseif (($col['type'] ?? '') === 'status')
                                    <span class="kk-pill kk-pill--{{ $raw === 'reviewed' ? 'review' : ($raw === 'hired' || $raw === 'approved' ? 'hired' : $raw) }}">{{ $raw === 'internship_offered' ? 'internship offered' : (in_array($raw, ['hired', 'approved'], true) ? 'approved' : $raw) }}</span>
                                @elseif (($col['type'] ?? '') === 'bool')
                                    <span class="kk-pill {{ $raw ? 'kk-pill--active' : 'kk-pill--closed' }}">{{ $raw ? 'Yes' : 'No' }}</span>
                                @elseif (($col['type'] ?? '') === 'bool_open')
                                    <span class="kk-pill {{ $raw ? 'kk-pill--open' : 'kk-pill--closed' }}">{{ $raw ? 'Open' : 'Closed' }}</span>
                                @elseif (($col['type'] ?? '') === 'date')
                                    <span class="kk-muted">{{ $raw ? \Illuminate\Support\Carbon::parse($raw)->format('d M Y') : '—' }}</span>
                                @elseif (($col['type'] ?? '') === 'datetime')
                                    <span class="kk-muted">{{ $raw ? \Illuminate\Support\Carbon::parse($raw)->format('d M Y, h:i A') : '—' }}</span>
                                @else
                                    <span class="{{ $loop->first ? 'kk-row-title' : '' }}">{{ $raw ?? '—' }}</span>
                                @endif
                            </td>
                        @endforeach
                        <td>
                            <div class="kk-actions">
                                <a class="kk-btn kk-btn-primary kk-btn-sm" href="{{ route('admin.module.show', [$mod['key'], $item->id]) }}">{{ $isWorkflow ? 'Review' : 'View' }}</a>
                                @if ($isWorkflow)
                                    <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="hired">
                                        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit">Approve</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.applications.status', $item->id) }}" onsubmit="return confirm('Reject the full-time job and send an internship offer email?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="internship_offered">
                                        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" style="border-color:#99f6e4;color:#0f766e;background:#f0fdfa">Internship</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.applications.status', $item->id) }}" onsubmit="return confirm('Reject this application?')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Reject</button>
                                    </form>
                                @elseif ($isHrNotify)
                                    <form method="POST" action="{{ route('admin.interviews.notify-hr', $item->id) }}" class="kk-hr-single-send" onsubmit="return window.kkFillHrFromBulk(this)">
                                        @csrf
                                        <input type="hidden" name="hr_email" value="{{ $item->hr_email }}">
                                        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit" style="border-color:#99f6e4;color:#0f766e;background:#f0fdfa">Send to HR</button>
                                    </form>
                                    <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.module.edit', [$mod['key'], $item->id]) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.module.destroy', [$mod['key'], $item->id]) }}" onsubmit="return confirm('Delete this record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                    </form>
                                @else
                                    <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.module.edit', [$mod['key'], $item->id]) }}">Edit</a>
                                    <form method="POST" action="{{ route('admin.module.destroy', [$mod['key'], $item->id]) }}" onsubmit="return confirm('Delete this record?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="kk-btn kk-btn-danger kk-btn-sm" type="submit">Delete</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($mod['columns']) + ($hasChecks ? 2 : 1) }}">
                            <div class="kk-empty">
                                <strong>No records yet</strong>
                                @if ($isHrNotify)
                                    Set Interview status from Job Applications, or schedule one with Add New.
                                @else
                                    When someone applies from the website, it will show up here.
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($items->hasPages())
        <div class="kk-pagination">
            @if ($items->onFirstPage())
                <span>‹</span>
            @else
                <a href="{{ $items->previousPageUrl() }}">‹</a>
            @endif
            @foreach ($items->getUrlRange(max(1, $items->currentPage()-2), min($items->lastPage(), $items->currentPage()+2)) as $page => $url)
                @if ($page == $items->currentPage())
                    <span class="active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
            @if ($items->hasMorePages())
                <a href="{{ $items->nextPageUrl() }}">›</a>
            @else
                <span>›</span>
            @endif
        </div>
    @endif
</section>
@endsection

@if ($hasChecks)
@push('scripts')
<script>
(() => {
  const all = document.getElementById('checkAll');
  const boxes = () => [...document.querySelectorAll('.row-check')];
  const syncCount = (id) => {
    const el = document.getElementById(id);
    if (el) el.textContent = boxes().filter(b => b.checked).length + ' selected';
  };
  const fillIds = (wrapId) => {
    const wrap = document.getElementById(wrapId);
    if (!wrap) return;
    wrap.innerHTML = '';
    boxes().filter(b => b.checked).forEach(b => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = 'ids[]';
      input.value = b.value;
      wrap.appendChild(input);
    });
  };
  const refresh = () => {
    syncCount('selectedCount');
    syncCount('hrSelectedCount');
    if (all) {
      const n = boxes().filter(b => b.checked).length;
      all.checked = n > 0 && n === boxes().length;
    }
  };
  all?.addEventListener('change', () => {
    boxes().forEach(b => { b.checked = all.checked; });
    refresh();
  });
  boxes().forEach(b => b.addEventListener('change', refresh));
  document.getElementById('bulkForm')?.addEventListener('submit', (e) => {
    fillIds('bulkIds');
    if (!boxes().some(b => b.checked)) {
      e.preventDefault();
      alert('Select at least 1 application first.');
    }
  });
  document.getElementById('hrBulkForm')?.addEventListener('submit', (e) => {
    fillIds('hrBulkIds');
    const hrEmail = document.querySelector('#hrPickerBulk .kk-hr-picker__email')?.value?.trim();
    if (!hrEmail) {
      e.preventDefault();
      alert('Search and select an HR contact first.');
      return;
    }
    if (!boxes().some(b => b.checked)) {
      e.preventDefault();
      alert('Select at least 1 interview first.');
      return;
    }
    if (!confirm('Send selected interview(s) to selected HR?')) e.preventDefault();
  });

  window.kkFillHrFromBulk = function (form) {
    const picked = document.querySelector('#hrPickerBulk .kk-hr-picker__email')?.value?.trim();
    const hidden = form.querySelector('input[name="hr_email"]');
    if (picked) {
      hidden.value = picked;
    }
    if (!hidden.value) {
      alert('Search and select HR above, then click Send to HR.');
      return false;
    }
    return confirm('Send this interview to ' + hidden.value + '?');
  };

  refresh();
})();
</script>
@include('admin.partials.hr-picker-script')
@endpush
@endif
