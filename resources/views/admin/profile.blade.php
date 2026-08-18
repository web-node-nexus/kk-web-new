@extends('layouts.admin')
@section('title', 'My profile | Admin')

@section('content')
@php $u = $user; @endphp
<div class="kk-pagehead">
    <div>
        <h1>My profile</h1>
        <p>Apni photo aur naam yahan se update karo. Top-right pe bhi yehi dikhegi.</p>
    </div>
</div>

<section class="kk-card" style="max-width:640px">
    <div class="kk-card__body">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:22px">
            @if ($u->avatarUrl())
                <img src="{{ $u->avatarUrl() }}" alt="" class="kk-profile__avatar" style="width:72px;height:72px;font-size:22px">
            @else
                <div class="kk-profile__avatar" style="width:72px;height:72px;font-size:22px">{{ $u->initials() }}</div>
            @endif
            <div>
                <strong style="display:block;font-size:16px">{{ $u->name }}</strong>
                <span class="kk-muted" style="font-size:13px">{{ $u->email }}</span>
                <div style="margin-top:10px;display:flex;flex-wrap:wrap;gap:8px">
                    <form method="POST" action="{{ route('admin.profile.avatar') }}" enctype="multipart/form-data" data-no-ajax>
                        @csrf
                        <label class="kk-btn kk-btn-primary kk-btn-sm" style="cursor:pointer;margin:0">
                            {{ $u->avatarUrl() ? 'Change photo' : 'Add photo' }}
                            <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" hidden onchange="this.form.submit()">
                        </label>
                    </form>
                    @if ($u->avatarUrl())
                        <form method="POST" action="{{ route('admin.profile.avatar.destroy') }}" onsubmit="return confirm('Photo hataani hai?')" data-no-ajax>
                            @csrf
                            @method('DELETE')
                            <button class="kk-btn kk-btn-secondary kk-btn-sm" type="submit">Remove</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <form class="kk-form" method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PUT')
            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Name</label>
                    <input type="text" name="name" value="{{ old('name', $u->name) }}" required maxlength="120">
                </div>
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Email</label>
                    <input type="email" value="{{ $u->email }}" readonly>
                </div>
                <div class="kk-field">
                    <label>Role</label>
                    <input type="text" value="{{ $u->adminRole?->name ?? 'Admin' }}" readonly>
                </div>
            </div>
            <div class="kk-form-actions">
                <button class="kk-btn kk-btn-primary" type="submit">Save name</button>
            </div>
        </form>

        <form class="kk-form" method="POST" action="{{ route('admin.profile.password') }}" style="margin-top:28px">
            @csrf
            @method('PUT')
            <h3 style="margin:0 0 12px">Change password</h3>
            <div class="kk-form-grid">
                <div class="kk-field" style="grid-column:1/-1">
                    <label>Current password</label>
                    <input type="password" name="current_password" required autocomplete="current-password">
                    @error('current_password')<p style="margin:6px 0 0;font-size:12px;color:#b91c1c">{{ $message }}</p>@enderror
                </div>
                <div class="kk-field">
                    <label>New password</label>
                    <input type="password" name="password" required minlength="8" autocomplete="new-password">
                </div>
                <div class="kk-field">
                    <label>Confirm password</label>
                    <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                </div>
            </div>
            <div class="kk-form-actions">
                <button class="kk-btn kk-btn-primary" type="submit">Update password</button>
            </div>
        </form>
    </div>
</section>
@endsection
