<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Employee Panel') | KK Digital</title>
    <link rel="icon" href="{{ asset('images/kk-logo.png') }}">
    <link rel="stylesheet" href="{{ asset('css/kk-employee.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    @stack('head')
</head>
@php
    $authUser = auth()->user();
    $authUser?->loadMissing('adminRole');
    $emp = $authUser?->employee;
    $initials = collect(explode(' ', $authUser->name ?? 'E'))
        ->filter()
        ->take(2)
        ->map(fn ($p) => strtoupper(substr($p, 0, 1)))
        ->implode('');
    $unreadNoteCount = 0;
    $latestNotes = collect();
    if ($emp && \Illuminate\Support\Facades\Schema::hasTable('employee_notifications')) {
        $unreadNoteCount = \App\Models\EmployeeNotification::query()
            ->where('employee_id', $emp->id)
            ->whereNull('read_at')
            ->count();
        $latestNotes = \App\Models\EmployeeNotification::query()
            ->where('employee_id', $emp->id)
            ->latest()
            ->limit(6)
            ->get();
    }
@endphp
<body class="ep-body">
<div class="ep-backdrop" id="epBackdrop"></div>
<div class="ep-app">
    <aside class="ep-side" id="epSide">
        <a class="ep-brand" href="{{ route('employee.dashboard') }}">
            <img src="{{ asset('images/kk-logo.png') }}" alt="KK Digital">
            <div>
                <strong>KK Digital</strong>
                <span>Employee Workspace</span>
            </div>
        </a>

        <div class="ep-userchip">
        <div class="ep-avatar">
            @if ($emp?->photoUrl())
                <img src="{{ $emp->photoUrl() }}" alt="" style="width:100%;height:100%;object-fit:cover;border-radius:inherit">
            @else
                {{ $initials ?: 'E' }}
            @endif
        </div>
            <div>
                <strong>{{ $authUser->name }}</strong>
                <em>{{ $authUser->adminRole?->name ?: ($emp->role_title ?? 'Team member') }}</em>
            </div>
        </div>

        <nav class="ep-nav">
            <a href="{{ route('employee.dashboard') }}" class="{{ request()->routeIs('employee.dashboard') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z"/></svg>
                Dashboard
            </a>
            @if ($authUser?->canEmployee('employee.attendance'))
            <a href="{{ route('employee.attendance') }}" class="{{ request()->routeIs('employee.attendance') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                Attendance
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.leaves'))
            <a href="{{ route('employee.leaves') }}" class="{{ request()->routeIs('employee.leaves') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v3M16 2v3M4 9h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/></svg>
                Leaves
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.interviews'))
            <a href="{{ route('employee.interviews') }}" class="{{ request()->routeIs('employee.interviews*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 7V3m8 4V3M4 11h16M6 5h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2z"/><path d="M9 15h.01M12 15h.01M15 15h.01"/></svg>
                Assigned Interviews
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.payroll'))
            <a href="{{ route('employee.payroll') }}" class="{{ request()->routeIs('employee.payroll') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18M8 15h4"/></svg>
                Payroll
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.announcements'))
            <a href="{{ route('employee.announcements') }}" class="{{ request()->routeIs('employee.announcements') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 11 19-7v14L3 11z"/><path d="M11.6 16.8a3 3 0 1 1-5.2-3"/></svg>
                Announcements
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.tasks'))
            <a href="{{ route('employee.tasks.index') }}" class="{{ request()->routeIs('employee.tasks*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                My Tasks
            </a>
            @endif
            @if ($authUser?->canEmployee('employee.chat'))
            <a href="{{ route('employee.chat.index') }}" class="{{ request()->routeIs('employee.chat*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                Live Chat
            </a>
            @endif
            @if ($authUser?->hasAnyEmployeeModule())
            <a href="{{ route('employee.notifications.index') }}" class="{{ request()->routeIs('employee.notifications*') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>
                Notifications
                @if (!empty($unreadNoteCount))
                    <em>{{ $unreadNoteCount > 99 ? '99+' : $unreadNoteCount }}</em>
                @endif
            </a>
            @endif
            <a href="{{ route('employee.profile') }}" class="{{ request()->routeIs('employee.profile') ? 'active' : '' }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 20a8 8 0 0 1 16 0"/></svg>
                My Profile
            </a>
        </nav>

        <div class="ep-side__foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="ep-logout" type="submit">Sign out</button>
            </form>
        </div>
    </aside>

    <main class="ep-main">
        <div class="ep-topbar">
            <div>
                <button type="button" class="ep-mobile-toggle" id="epMenuBtn" aria-label="Open menu">Menu</button>
                <h1 style="margin-top:10px">@yield('heading', 'Dashboard')</h1>
                <p>@yield('subheading', 'Your workplace essentials in one place.')</p>
            </div>
            <div class="ep-topbar__right">
                @if ($authUser?->hasAnyEmployeeModule())
                <div class="ep-note-wrap">
                    <button type="button" class="ep-note-btn" id="epNoteBtn" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>
                        @if ($unreadNoteCount > 0)
                            <b>{{ $unreadNoteCount > 99 ? '99+' : $unreadNoteCount }}</b>
                        @endif
                    </button>
                    <div class="ep-note-drop" id="epNoteDrop" hidden>
                        <div class="ep-note-drop__h">
                            <strong>Notifications</strong>
                            <a href="{{ route('employee.notifications.index') }}">See all</a>
                        </div>
                        @forelse ($latestNotes as $note)
                            <a class="ep-note-drop__item {{ $note->isUnread() ? 'is-unread' : '' }}" href="{{ route('employee.notifications.open', $note->id) }}">
                                <strong>{{ $note->title }}</strong>
                                <span>{{ $note->body }}</span>
                                <em>{{ \App\Support\AppTime::formatShort($note->created_at) }}</em>
                            </a>
                        @empty
                            <p class="ep-muted" style="padding:12px 14px;margin:0">No notifications yet.</p>
                        @endforelse
                    </div>
                </div>
                @endif
                <div class="ep-badge">
                    {{ \App\Support\AppTime::now()->format('l, d M Y · h:i A') }}
                </div>
            </div>
        </div>

        @if (session('success'))
            <div class="ep-flash ep-flash--ok">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="ep-flash ep-flash--err">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </main>
</div>
<script>
    (function () {
        const side = document.getElementById('epSide');
        const btn = document.getElementById('epMenuBtn');
        const backdrop = document.getElementById('epBackdrop');
        const close = () => { side.classList.remove('is-open'); backdrop.classList.remove('is-open'); };
        const open = () => { side.classList.add('is-open'); backdrop.classList.add('is-open'); };
        if (btn) btn.addEventListener('click', open);
        if (backdrop) backdrop.addEventListener('click', close);
        const noteBtn = document.getElementById('epNoteBtn');
        const noteDrop = document.getElementById('epNoteDrop');
        noteBtn?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (!noteDrop) return;
            noteDrop.hidden = !noteDrop.hidden;
        });
        document.addEventListener('click', () => { if (noteDrop) noteDrop.hidden = true; });
        noteDrop?.addEventListener('click', (e) => e.stopPropagation());
    })();
</script>
<script src="{{ asset('js/kk-ajax.js') }}"></script>
<script src="{{ asset('js/kk-live.js') }}"></script>
@stack('scripts')
</body>
</html>
