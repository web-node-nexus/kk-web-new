@extends('layouts.admin')

@section('title', 'Assign task | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Assign task</h1>
        <p>All employees select karke bhejo, ya search karke ek / selected employees ko assign karo.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.tasks.index') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ route('admin.tasks.store') }}" id="taskForm" enctype="multipart/form-data">
            @csrf

            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Title *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="e.g. Complete weekly report">
                </div>

                <div class="kk-field">
                    <label>Priority *</label>
                    <select name="priority" required>
                        @foreach (['low' => 'Low', 'normal' => 'Normal', 'high' => 'High'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('priority', 'normal') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="kk-field">
                    <label>Due date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}">
                </div>
                <div class="kk-field">
                    <label>Due time</label>
                    <input type="time" name="due_time" value="{{ old('due_time', \App\Support\AppTime::now()->format('H:i')) }}">
                    <p class="kk-muted" style="margin:6px 0 0;font-size:12px">India time (IST). Jo time choose karoge wahi employee ko dikhega.</p>
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Details</label>
                    <div class="kk-editor" id="taskEditor">
                        <div class="kk-editor__bar">
                            <button type="button" data-cmd="bold"><b>B</b></button>
                            <button type="button" data-cmd="italic"><i>I</i></button>
                            <button type="button" data-cmd="underline"><u>U</u></button>
                            <button type="button" data-cmd="insertUnorderedList">•</button>
                            <button type="button" data-cmd="insertOrderedList">1.</button>
                        </div>
                        <div class="kk-editor__area" id="taskEditorArea" data-editor-source="taskDescription" contenteditable="true">{!! old('description') !!}</div>
                    </div>
                    <textarea name="description" id="taskDescription" hidden>{{ old('description') }}</textarea>
                </div>

                @include('admin.tasks._media-fields')

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Assign to *</label>
                    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:6px">
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;font-weight:600">
                            <input type="radio" name="assign_mode" value="all" id="modeAll" @checked(old('assign_mode', 'all') === 'all')>
                            All employees
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;font-weight:600">
                            <input type="radio" name="assign_mode" value="selected" id="modeSelected" @checked(old('assign_mode') === 'selected')>
                            Search / select employees
                        </label>
                    </div>
                </div>

                <div class="kk-field" style="grid-column:1/-1" id="employeeWrap">
                    <label>Employees</label>
                    <div style="display:flex;gap:10px;flex-wrap:wrap;margin:8px 0 12px;align-items:center">
                        <input type="search" id="empSearch" placeholder="Search name, email, role…" style="flex:1;min-width:220px">
                        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="button" id="selectVisible">Select all visible</button>
                        <button class="kk-btn kk-btn-secondary kk-btn-sm" type="button" id="clearVisible">Clear</button>
                        <span class="kk-muted" id="selCount">0 selected</span>
                    </div>
                    <div id="empList" class="kk-emp-pick">
                        @php $oldIds = collect(old('employee_ids', []))->map(fn ($id) => (string) $id); @endphp
                        @forelse ($employees as $emp)
                            <label class="kk-emp-pick__row" data-search="{{ strtolower($emp->name.' '.$emp->email.' '.$emp->role_title) }}">
                                <input type="checkbox" name="employee_ids[]" value="{{ $emp->id }}" @checked($oldIds->contains((string) $emp->id))>
                                <span>
                                    <strong>{{ $emp->name }}</strong>
                                    <em>{{ $emp->email }}@if($emp->role_title) · {{ $emp->role_title }}@endif</em>
                                </span>
                            </label>
                        @empty
                            <p class="kk-muted">No active employees found.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="kk-form-actions">
                <a href="{{ route('admin.tasks.index') }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">Assign task</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const all = document.getElementById('modeAll');
  const selected = document.getElementById('modeSelected');
  const wrap = document.getElementById('employeeWrap');
  const search = document.getElementById('empSearch');
  const list = document.getElementById('empList');
  const selCount = document.getElementById('selCount');
  const rows = () => [...document.querySelectorAll('.kk-emp-pick__row')];

  const syncMode = () => {
    wrap.style.display = selected?.checked ? '' : 'none';
  };
  const updateCount = () => {
    const n = document.querySelectorAll('input[name="employee_ids[]"]:checked').length;
    selCount.textContent = n + ' selected';
  };
  const filter = () => {
    const q = (search.value || '').trim().toLowerCase();
    rows().forEach((row) => {
      const hay = row.getAttribute('data-search') || '';
      row.style.display = !q || hay.includes(q) ? '' : 'none';
    });
  };

  all?.addEventListener('change', syncMode);
  selected?.addEventListener('change', syncMode);
  search?.addEventListener('input', filter);
  list?.addEventListener('change', updateCount);
  document.getElementById('selectVisible')?.addEventListener('click', () => {
    rows().forEach((row) => {
      if (row.style.display === 'none') return;
      const box = row.querySelector('input[type="checkbox"]');
      if (box) box.checked = true;
    });
    updateCount();
  });
  document.getElementById('clearVisible')?.addEventListener('click', () => {
    rows().forEach((row) => {
      if (row.style.display === 'none') return;
      const box = row.querySelector('input[type="checkbox"]');
      if (box) box.checked = false;
    });
    updateCount();
  });
  syncMode();
  updateCount();

  const area = document.getElementById('taskEditorArea');
  const hidden = document.getElementById('taskDescription');
  document.querySelectorAll('#taskEditor [data-cmd]').forEach((btn) => {
    btn.addEventListener('click', () => {
      document.execCommand(btn.getAttribute('data-cmd'), false, null);
      area?.focus();
    });
  });
  document.getElementById('taskForm')?.addEventListener('submit', () => {
    if (hidden && area) hidden.value = area.innerHTML;
  });
})();
</script>
@endpush
