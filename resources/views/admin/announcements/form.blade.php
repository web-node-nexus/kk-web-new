@extends('layouts.admin')

@section('title', ($mode === 'create' ? 'New' : 'Edit').' Announcement | Admin')

@section('content')
@php $item = $item ?? null; @endphp
<div class="kk-pagehead">
    <div>
        <h1>{{ $mode === 'create' ? 'New announcement' : 'Edit announcement' }}</h1>
        <p>Send to all employees or one specific employee.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.announcements.index') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div style="margin-bottom:16px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:12px">{{ $errors->first() }}</div>
@endif

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ $mode === 'create' ? route('admin.announcements.store') : route('admin.announcements.update', $item->id) }}" id="annForm" enctype="multipart/form-data">
            @csrf
            @if ($mode === 'edit') @method('PUT') @endif

            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Title *</label>
                    <input type="text" name="title" value="{{ old('title', $item->title ?? '') }}" required placeholder="e.g. Office holiday notice">
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Send to *</label>
                    <div style="display:flex;flex-wrap:wrap;gap:12px;margin-top:6px">
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;font-weight:600">
                            <input type="radio" name="audience_type" value="all" id="audAll"
                                @checked(old('audience_type', $item->audience_type ?? 'all') === 'all')>
                            All employees (Everyone)
                        </label>
                        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid #e2e8f0;border-radius:10px;cursor:pointer;font-weight:600">
                            <input type="radio" name="audience_type" value="one" id="audOne"
                                @checked(old('audience_type', $item->audience_type ?? '') === 'one')>
                            One employee
                        </label>
                    </div>
                </div>

                <div class="kk-field" style="grid-column:1/-1" id="employeeWrap">
                    <label>Select employee *</label>
                    <select name="employee_id" id="employee_id">
                        <option value="">Choose employee…</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}" @selected((string)old('employee_id', $item->employee_id ?? '') === (string)$emp->id)>
                                {{ $emp->name }} — {{ $emp->email }}@if($emp->role_title) ({{ $emp->role_title }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="kk-field">
                    <label>Status *</label>
                    <select name="status" required>
                        @foreach (['draft', 'published'] as $st)
                            <option value="{{ $st }}" @selected(old('status', $item->status ?? 'published') === $st)>{{ ucfirst($st) }}</option>
                        @endforeach
                    </select>
                    <p class="kk-muted" style="margin:6px 0 0;font-size:12px">Published announcements appear in the employee panel.</p>
                </div>

                <div class="kk-field">
                    <label>Link (optional)</label>
                    <input type="url" name="link_url" value="{{ old('link_url', $item->link_url ?? '') }}" placeholder="https://example.com/details">
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Image (optional)</label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp">
                    @if ($item?->imageUrl())
                        <div style="margin-top:10px;display:flex;align-items:center;gap:12px">
                            <img src="{{ $item->imageUrl() }}" alt="" style="width:96px;height:96px;object-fit:cover;border-radius:12px;border:1px solid #e2e8f0">
                            <label style="display:flex;align-items:center;gap:8px;font-size:13px">
                                <input type="checkbox" name="remove_image" value="1"> Remove current image
                            </label>
                        </div>
                    @endif
                </div>

                <div class="kk-field" style="grid-column:1/-1">
                    <label>Message *</label>
                    <textarea name="body" rows="8" required placeholder="Write the announcement…">{{ old('body', $item->body ?? '') }}</textarea>
                </div>
            </div>

            <div class="kk-form-actions">
                <a href="{{ route('admin.announcements.index') }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">{{ $mode === 'create' ? 'Post announcement' : 'Save changes' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const all = document.getElementById('audAll');
  const one = document.getElementById('audOne');
  const wrap = document.getElementById('employeeWrap');
  const sel = document.getElementById('employee_id');
  const sync = () => {
    const isOne = one?.checked;
    wrap.style.display = isOne ? '' : 'none';
    if (sel) sel.required = !!isOne;
  };
  all?.addEventListener('change', sync);
  one?.addEventListener('change', sync);
  sync();
})();
</script>
@endpush
