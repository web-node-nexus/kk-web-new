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
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    class="glass-input"
                    placeholder="Enter your password"
                >
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
        <p class="mt-3 text-center text-[0.75rem] text-white/45">
            Admin: admin@kkdigital.com / Admin@123
        </p>
    </div>
</div>
@endsection
