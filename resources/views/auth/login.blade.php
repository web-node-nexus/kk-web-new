@extends('layouts.auth')

@section('title', 'Login | KK Digital Solution')

@section('content')
<div class="glass-auth">
    <div class="glass-auth__orb glass-auth__orb--1"></div>
    <div class="glass-auth__orb glass-auth__orb--2"></div>

    <div class="glass-card">
        <div class="mb-7">
            <a href="{{ route('home') }}" class="tn-logo tn-logo--auth" aria-label="KK Digital Solution">
                <img src="{{ asset('images/kk-logo.png') }}" alt="KK Digital Solution" width="56" height="56">
            </a>
        </div>

        <h1>Sign in</h1>
        <p class="sub">Admin or Employee panel — use your work email</p>

        @if ($errors->any())
            <div class="glass-error mt-5">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label class="glass-label" for="email">Work email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email', 'admin@kkdigital.com') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="glass-input"
                    placeholder="you@company.com"
                >
            </div>
            <div>
                <label class="glass-label" for="password">Password</label>
                <div class="kk-pass-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="glass-input"
                        placeholder="Enter your password"
                    >
                    <button type="button" class="kk-pass-toggle" data-pass-toggle aria-label="Show password" title="Show password">
                        <svg class="kk-eye-open" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="kk-eye-closed" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" hidden><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex items-center justify-between pt-1">
                <label class="glass-check">
                    <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                    Remember me
                </label>
                <span class="text-xs text-white/55">Secure login</span>
            </div>
            <button type="submit" class="glass-btn mt-2">Sign in</button>
        </form>

        <p class="glass-meta">
            Back to <a href="{{ route('home') }}">website</a>
        </p>
    </div>
</div>
@endsection
