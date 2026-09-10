@extends('layouts.employee')
@section('title', 'My Profile')
@section('heading', 'My Profile')
@section('subheading', 'Your photo, employment details, and password.')

@section('content')
@php
    $emp = $employee;
    $initials = collect(explode(' ', $user->name ?? 'E'))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
@endphp

<div class="ep-profile">
    <div class="ep-profile__card">
        <div class="ep-profile__banner" aria-hidden="true"></div>
        <div class="ep-profile__avatar">
            @if ($emp?->photoUrl())
                <img src="{{ $emp->photoUrl() }}?v={{ optional($emp->updated_at)->timestamp }}" alt="{{ $user->name }}">
            @else
                {{ $initials ?: 'E' }}
            @endif
        </div>
        <h3 class="ep-profile__name">{{ $user->name }}</h3>
        <p class="ep-profile__role">{{ $emp->role_title ?? 'Team member' }}</p>
        @if ($emp?->employee_code)
            <p class="ep-profile__code">{{ $emp->employee_code }}</p>
        @endif
        @if ($emp?->status)
            <p class="ep-profile__status"><span class="ep-pill ep-pill--ok">{{ $emp->status }}</span></p>
        @endif

        @if ($emp)
            <div class="ep-profile__actions">
                <form method="POST" action="{{ route('employee.profile.photo') }}" enctype="multipart/form-data" data-no-ajax>
                    @csrf
                    <label class="ep-btn ep-btn-primary ep-profile__upload">
                        {{ $emp->photoUrl() ? 'Change photo' : 'Upload photo' }}
                        <input type="file" name="photo" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" hidden onchange="this.form.submit()">
                    </label>
                </form>
                @if ($emp->photoUrl())
                    <form method="POST" action="{{ route('employee.profile.photo.destroy') }}" data-no-ajax>
                        @csrf
                        @method('DELETE')
                        <button class="ep-btn" type="submit" onclick="return confirm('Remove your profile photo?')">Remove photo</button>
                    </form>
                @endif
            </div>
            <p class="ep-profile__hint">Square photos work best. Your image stays cropped inside the frame.</p>
        @endif
    </div>

    <div class="ep-profile__main">
        <div class="ep-card">
            <div class="ep-card__h"><h3>Employment details</h3></div>
            <div class="ep-card__b">
                @if ($emp)
                    <div class="ep-detail-grid">
                        <div class="ep-detail"><span>Employee code</span><strong>{{ $emp->employee_code ?: '—' }}</strong></div>
                        <div class="ep-detail"><span>Email</span><strong>{{ $emp->email }}</strong></div>
                        <div class="ep-detail"><span>Phone</span><strong>{{ $emp->phone ?: '—' }}</strong></div>
                        <div class="ep-detail"><span>Department</span><strong>{{ $emp->department->name ?? '—' }}</strong></div>
                        <div class="ep-detail"><span>Role</span><strong>{{ $emp->role_title ?: '—' }}</strong></div>
                        <div class="ep-detail"><span>Employment type</span><strong>{{ $emp->employment_type ?: '—' }}</strong></div>
                        <div class="ep-detail"><span>Job type</span><strong>{{ $emp->job_type ?: '—' }}</strong></div>
                        <div class="ep-detail"><span>Join date</span><strong>{{ optional($emp->join_date)->format('d M Y') ?: '—' }}</strong></div>
                    </div>
                @else
                    <div class="ep-empty">No employee profile linked.</div>
                @endif
            </div>
        </div>

        <div class="ep-card">
            <div class="ep-card__h"><h3>Change password</h3></div>
            <div class="ep-card__b">
                <form class="ep-form ep-form--2" method="POST" action="{{ route('employee.password') }}">
                    @csrf
                    <div class="ep-field" style="grid-column:1/-1">
                        <label for="current_password">Current password</label>
                        <div class="kk-pass-wrap">
                            <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                            <button type="button" class="kk-pass-toggle" data-pass-toggle aria-label="Show password">👁</button>
                        </div>
                    </div>
                    <div class="ep-field">
                        <label for="password">New password</label>
                        <div class="kk-pass-wrap">
                            <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                            <button type="button" class="kk-pass-toggle" data-pass-toggle aria-label="Show password">👁</button>
                        </div>
                    </div>
                    <div class="ep-field">
                        <label for="password_confirmation">Confirm new password</label>
                        <div class="kk-pass-wrap">
                            <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                            <button type="button" class="kk-pass-toggle" data-pass-toggle aria-label="Show password">👁</button>
                        </div>
                    </div>
                    <div style="grid-column:1/-1">
                        <button class="ep-btn ep-btn-primary" type="submit">Update password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
