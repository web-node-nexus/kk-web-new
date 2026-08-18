{{-- Searchable HR employee picker. Expects: $inputName, $selectedEmail (optional), $required (optional), $pickerId (unique) --}}
@php
    $inputName = $inputName ?? 'hr_email';
    $selectedEmail = old($inputName, $selectedEmail ?? '');
    $required = $required ?? false;
    $pickerId = $pickerId ?? ('hrp_'.uniqid());
    $searchUrl = route('admin.employees.search-hr');
@endphp
<div class="kk-hr-picker" data-hr-picker id="{{ $pickerId }}" data-search-url="{{ $searchUrl }}" style="position:relative">
    <input type="hidden" name="{{ $inputName }}" value="{{ $selectedEmail }}" class="kk-hr-picker__email" @if($required) required @endif>
    <div class="kk-hr-picker__selected" style="{{ $selectedEmail ? 'display:flex;align-items:center;gap:10px;flex-wrap:wrap;padding:9px 12px;border:1px solid #99f6e4;border-radius:10px;background:#f0fdfa' : 'display:none' }}">
        <strong class="kk-hr-picker__selected-label" style="flex:1;font-size:13px;color:#0f766e">{{ $selectedEmail }}</strong>
        <button type="button" class="kk-btn kk-btn-ghost kk-btn-sm kk-hr-picker__clear">Change</button>
    </div>
    <div class="kk-hr-picker__search-wrap" style="{{ $selectedEmail ? 'display:none' : 'position:relative' }}">
        <input
            type="search"
            class="kk-hr-picker__search"
            placeholder="Type name / email / role / code to find HR…"
            autocomplete="off"
            style="width:100%;min-width:260px;padding:9px 12px;border:1px solid #cbd5e1;border-radius:10px"
        >
        <div class="kk-hr-picker__results" style="display:none;position:absolute;z-index:50;left:0;right:0;max-height:280px;overflow:auto;background:#fff;border:1px solid #e2e8f0;border-radius:12px;box-shadow:0 12px 30px rgba(15,23,42,.12);margin-top:6px"></div>
    </div>
</div>
