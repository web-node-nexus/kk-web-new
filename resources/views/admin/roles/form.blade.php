@extends('layouts.admin')

@section('title', ($mode === 'create' ? 'Add' : 'Edit').' Role | Admin')

@section('content')
@php
    $isFullAdmin = $role && ($role->hasFullAccess() || $role->slug === 'admin');
    $checkedPerms = old('permissions', $selected);
    if (! is_array($checkedPerms)) {
        $checkedPerms = is_array($selected) ? $selected : [];
    }
@endphp
<div class="kk-pagehead">
    <div>
        <h1>{{ $mode === 'create' ? 'Add Role' : 'Edit Role' }}</h1>
        <p>Tick the pages this role can open. <strong>Employee panel</strong> group me jo tick hoga wahi employee login me dikhega. Agar wahan kuch tick nahi kiya, employee panel me modules nahi dikhenge.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.roles.index') }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

@if ($errors->any())
    <div style="margin-bottom:16px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:12px">
        {{ $errors->first() }}
    </div>
@endif

<section class="kk-card">
    <div class="kk-card__body">
        <form class="kk-form" method="POST" action="{{ $mode === 'create' ? route('admin.roles.store') : route('admin.roles.update', $role->id) }}">
            @csrf
            @if ($mode === 'edit')
                @method('PUT')
            @endif

            <div class="kk-form-grid">
                <div class="kk-field">
                    <label>Role name *</label>
                    <input type="text" name="name" value="{{ old('name', $isFullAdmin ? 'Admin' : ($role?->name ?? '')) }}" required placeholder="e.g. HR Manager" @disabled($isFullAdmin)>
                    @if ($isFullAdmin)<input type="hidden" name="name" value="Admin">@endif
                </div>
                <div class="kk-field">
                    <label>Slug</label>
                    <input type="text" name="slug" value="{{ old('slug', $role?->slug ?? '') }}" @disabled($isFullAdmin) placeholder="auto from name">
                    @if ($isFullAdmin)<input type="hidden" name="slug" value="admin">@endif
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Description</label>
                    <input type="text" name="description" value="{{ old('description', $role?->description ?? '') }}" placeholder="Short note about this role">
                </div>
            </div>

            <div style="margin-top:22px">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:12px">
                    <h2 style="margin:0;font-size:15px">Permissions</h2>
                    @unless ($isFullAdmin)
                        <label style="display:flex;align-items:center;gap:8px;font-size:13px;font-weight:600;cursor:pointer">
                            <input type="checkbox" id="permSelectAll"> Select all
                        </label>
                    @endunless
                </div>

                @if ($isFullAdmin)
                    <div style="padding:14px 16px;border:1px solid #99f6e4;background:#f0fdfa;border-radius:12px;color:#0f766e;font-size:13px">
                        <strong>Admin</strong> ke paas poora panel access hai — har page. Limited roles alag se banao (HR, Accounts, etc.).
                        <input type="hidden" name="full_access" value="1">
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px">
                        @foreach ($catalog as $group)
                            <div style="border:1px solid #e2e8f0;border-radius:14px;padding:14px 14px 8px;background:#fff">
                                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px">
                                    <strong style="font-size:13px">{{ $group['label'] }}</strong>
                                    <button type="button" class="kk-btn kk-btn-ghost kk-btn-sm kk-group-toggle" data-group="{{ $loop->index }}">All</button>
                                </div>
                                @foreach ($group['items'] as $key => $label)
                                    <label style="display:flex;align-items:center;gap:8px;padding:6px 0;font-size:13px;font-weight:500;cursor:pointer">
                                        <input
                                            type="checkbox"
                                            class="perm-check group-{{ $loop->parent->index }}"
                                            name="permissions[]"
                                            value="{{ $key }}"
                                            @checked(in_array($key, $checkedPerms, true))
                                        >
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div style="margin-top:22px">
                <h2 style="margin:0 0 8px;font-size:15px">Assign admin users</h2>
                <p class="kk-muted" style="margin:0 0 12px;font-size:13px">In admins select karo jinko ye role milega.</p>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px;max-height:220px;overflow:auto;border:1px solid #e2e8f0;border-radius:12px;padding:10px">
                    @forelse ($admins as $admin)
                        <label style="display:flex;align-items:flex-start;gap:8px;font-size:13px;padding:6px;border-radius:8px;cursor:pointer">
                            <input type="checkbox" name="user_ids[]" value="{{ $admin->id }}" @checked(in_array($admin->id, old('user_ids', $assignedIds), false))>
                            <span>
                                <strong style="display:block">{{ $admin->name }}</strong>
                                <span class="kk-muted">{{ $admin->email }}</span>
                            </span>
                        </label>
                    @empty
                        <p class="kk-muted" style="margin:8px">No admin users yet. Create one from the Roles page.</p>
                    @endforelse
                </div>
            </div>

            <div style="margin-top:22px">
                <h2 style="margin:0 0 8px;font-size:15px">Assign employees</h2>
                <p class="kk-muted" style="margin:0 0 12px;font-size:13px">In employees ko ye role do. Unke panel me sirf <strong>Employee panel</strong> group ki tick wali cheezein dikhengi. Koi tick nahi = modules nahi dikhenge.</p>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:8px;max-height:260px;overflow:auto;border:1px solid #e2e8f0;border-radius:12px;padding:10px">
                    @forelse ($employees ?? [] as $empUser)
                        <label style="display:flex;align-items:flex-start;gap:8px;font-size:13px;padding:6px;border-radius:8px;cursor:pointer">
                            <input type="checkbox" name="user_ids[]" value="{{ $empUser->id }}" @checked(in_array($empUser->id, old('user_ids', $assignedIds), false))>
                            <span>
                                <strong style="display:block">{{ $empUser->employee?->name ?: $empUser->name }}</strong>
                                <span class="kk-muted">{{ $empUser->email }}@if($empUser->employee?->role_title) · {{ $empUser->employee->role_title }}@endif</span>
                            </span>
                        </label>
                    @empty
                        <p class="kk-muted" style="margin:8px">No employee logins yet. Employee add karne par yahan aa jayega.</p>
                    @endforelse
                </div>
            </div>

            <div class="kk-form-actions" style="margin-top:22px">
                <a href="{{ route('admin.roles.index') }}" class="kk-btn kk-btn-secondary">Cancel</a>
                <button class="kk-btn kk-btn-primary" type="submit">{{ $mode === 'create' ? 'Create role' : 'Save role' }}</button>
            </div>
        </form>
    </div>
</section>
@endsection

@push('scripts')
<script>
(() => {
  const all = document.getElementById('permSelectAll');
  const boxes = () => [...document.querySelectorAll('.perm-check')];
  all?.addEventListener('change', () => boxes().forEach(b => { b.checked = all.checked; }));
  document.querySelectorAll('.kk-group-toggle').forEach(btn => {
    btn.addEventListener('click', () => {
      const g = btn.dataset.group;
      const groupBoxes = [...document.querySelectorAll('.group-' + g)];
      const turnOn = groupBoxes.some(b => !b.checked);
      groupBoxes.forEach(b => { b.checked = turnOn; });
    });
  });
})();
</script>
@endpush
