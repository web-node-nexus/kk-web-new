@extends('layouts.employee')
@section('title', 'My Profile')
@section('heading', 'My Profile')
@section('subheading', 'Personal details and account security.')

@section('content')
@php
    $emp = $employee;
    $initials = collect(explode(' ', $user->name ?? 'E'))->filter()->take(2)->map(fn ($p) => strtoupper(substr($p, 0, 1)))->implode('');
@endphp

<div class="ep-profile">
    <div class="ep-profile__card">
        <div class="ep-profile__avatar">
            @if ($emp?->photoUrl())
                <img src="{{ $emp->photoUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
            @else
                {{ $initials ?: 'E' }}
            @endif
        </div>
        <h3 style="margin:0;font-family:Sora,Manrope,sans-serif">{{ $user->name }}</h3>
        <p style="margin:6px 0 0;color:#64748b;font-size:13px">{{ $emp->role_title ?? 'Team member' }}</p>
        @if ($emp?->status)
            <p style="margin:12px 0 0"><span class="ep-pill ep-pill--ok">{{ $emp->status }}</span></p>
        @endif
    </div>

    <div>
        <div class="ep-card">
            <div class="ep-card__h"><h3>Employment details</h3></div>
            <div class="ep-card__b">
                @if ($emp)
                    <div class="ep-kv">
                        <span>Employee code</span><strong>{{ $emp->employee_code ?: '—' }}</strong>
                        <span>Email</span><strong>{{ $emp->email }}</strong>
                        <span>Phone</span><strong>{{ $emp->phone ?: '—' }}</strong>
                        <span>Department</span><strong>{{ $emp->department->name ?? '—' }}</strong>
                        <span>Role</span><strong>{{ $emp->role_title ?: '—' }}</strong>
                        <span>Employment type</span><strong>{{ $emp->employment_type ?: '—' }}</strong>
                        <span>Job type</span><strong>{{ $emp->job_type ?: '—' }}</strong>
                        <span>Join date</span><strong>{{ optional($emp->join_date)->format('d M Y') ?: '—' }}</strong>
                    </div>
                @else
                    <div class="ep-empty">No employee profile linked.</div>
                @endif
            </div>
        </div>

        <div class="ep-card">
            <div class="ep-card__h"><h3>Change password</h3></div>
            <div class="ep-card__b">
                <form class="ep-form" method="POST" action="{{ route('employee.password') }}">
                    @csrf
                    <div class="ep-field">
                        <label for="current_password">Current password</label>
                        <input id="current_password" type="password" name="current_password" required autocomplete="current-password">
                    </div>
                    <div class="ep-field">
                        <label for="password">New password</label>
                        <input id="password" type="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>
                    <div class="ep-field">
                        <label for="password_confirmation">Confirm new password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
                    </div>
                    <div>
                        <button class="ep-btn ep-btn-primary" type="submit">Update password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
