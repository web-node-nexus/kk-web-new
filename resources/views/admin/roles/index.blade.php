@extends('layouts.admin')

@section('title', 'Roles & Permissions | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Roles & Permissions</h1>
        <p>Create roles, tick permissions, and assign them to admins or employees. Employee panel me wahi dikhega jo tick hoga.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.roles.create') }}" class="kk-btn kk-btn-primary">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
            Add Role
        </a>
    </div>
</div>

@if (session('success'))
    <div class="kk-alert kk-alert-success" style="margin-bottom:16px">{{ session('success') }}</div>
@endif
@if ($errors->any())
    <div class="kk-alert" style="margin-bottom:16px;background:#fef2f2;color:#991b1b;border:1px solid #fecaca;padding:12px 14px;border-radius:12px">
        {{ $errors->first() }}
    </div>
@endif

<div style="display:grid;grid-template-columns:1.4fr 1fr;gap:16px;align-items:start">
    <section class="kk-card">
        <div class="kk-card__body">
            <h2 style="margin:0 0 14px;font-size:16px">Roles</h2>
            <div style="overflow:auto">
                <table class="kk-table">
                    <thead>
                        <tr>
                            <th>Role</th>
                            <th>Permissions</th>
                            <th>Users</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            @php
                                $perms = $role->permissionList();
                                $isFull = $role->hasFullAccess();
                                $count = $isFull ? 'Full access' : (count($perms).' selected');
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $role->name }}</strong>
                                    @if ($role->slug === 'admin' || $role->hasFullAccess())
                                        <span style="margin-left:6px;font-size:11px;font-weight:700;color:#0f766e;background:#f0fdfa;border:1px solid #99f6e4;border-radius:999px;padding:2px 8px">Full access</span>
                                    @endif
                                    <div class="kk-muted" style="font-size:12px;margin-top:2px">{{ $role->description ?: $role->slug }}</div>
                                </td>
                                <td><span class="kk-muted">{{ $count }}</span></td>
                                <td>{{ $role->users_count }}</td>
                                <td style="white-space:nowrap">
                                    <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ route('admin.roles.edit', $role->id) }}">Edit</a>
                                    @unless ($role->hasFullAccess() || $role->slug === 'admin' || $role->slug === 'employee')
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role->id) }}" style="display:inline" onsubmit="return confirm('Delete this role?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="kk-btn kk-btn-ghost kk-btn-sm" type="submit" style="color:#b91c1c">Delete</button>
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No roles yet. Create one.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    <div style="display:grid;gap:16px">
        <section class="kk-card">
            <div class="kk-card__body">
                <h2 style="margin:0 0 6px;font-size:16px">Assign role to admin / employee</h2>
                <p class="kk-muted" style="margin:0 0 14px;font-size:13px">Admin ya employee dono ko role de sakte ho. Employee ko milega to uske panel me wahi pages dikhenge.</p>
                <form method="POST" action="{{ route('admin.roles.assign') }}" class="kk-form" style="display:grid;gap:12px">
                    @csrf
                    <div class="kk-field">
                        <label>User</label>
                        <select name="user_id" required>
                            <option value="">Select user…</option>
                            <optgroup label="Admins">
                                @foreach ($admins as $admin)
                                    <option value="{{ $admin->id }}">{{ $admin->name }} ({{ $admin->email }}) — {{ $admin->adminRole?->name ?? 'No role' }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Employees">
                                @foreach ($employees ?? [] as $empUser)
                                    <option value="{{ $empUser->id }}">{{ $empUser->employee?->name ?: $empUser->name }} ({{ $empUser->email }}) — {{ $empUser->adminRole?->name ?? 'Employee' }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="kk-field">
                        <label>Role</label>
                        <select name="admin_role_id" required>
                            <option value="">Select role…</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="kk-btn kk-btn-primary" type="submit">Assign role</button>
                </form>
            </div>
        </section>

        <section class="kk-card">
            <div class="kk-card__body">
                <h2 style="margin:0 0 6px;font-size:16px">Add new admin user</h2>
                <p class="kk-muted" style="margin:0 0 14px;font-size:13px">Naya admin banao aur turant role do.</p>
                <form method="POST" action="{{ route('admin.roles.create-user') }}" class="kk-form" style="display:grid;gap:12px">
                    @csrf
                    <div class="kk-field">
                        <label>Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" required>
                    </div>
                    <div class="kk-field">
                        <label>Email (login)</label>
                        <input type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div class="kk-field">
                        <label>Password</label>
                        <input type="text" name="password" required minlength="6" autocomplete="off">
                    </div>
                    <div class="kk-field">
                        <label>Role</label>
                        <select name="admin_role_id" required>
                            <option value="">Select role…</option>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}" @selected((string)old('admin_role_id') === (string)$role->id)>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="kk-btn kk-btn-secondary" type="submit">Create admin + assign</button>
                </form>
            </div>
        </section>
    </div>
</div>

<style>
@media (max-width: 980px) {
    .kk-pagehead + div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
@endsection
