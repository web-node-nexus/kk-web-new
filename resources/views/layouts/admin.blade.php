<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin | KK Digital')</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/kk-admin.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" defer></script>
    @stack('head')
</head>
@php
    $u = $user ?? auth()->user();
    try {
        $inbox = \App\Support\AdminInbox::snapshot($u, 8);
    } catch (\Throwable) {
        $inbox = [
            'notifications' => collect(),
            'messages' => collect(),
            'counts' => [
                'applications' => 0,
                'contacts' => 0,
                'projects' => 0,
                'leaves' => 0,
                'chats' => 0,
                'task_replies' => 0,
            ],
            'notification_count' => 0,
            'message_count' => 0,
        ];
    }
    $newApps = $inbox['counts']['applications'] ?? 0;
    $newContacts = $inbox['counts']['contacts'] ?? 0;
    $newProjects = $inbox['counts']['projects'] ?? 0;
    $pendingLeaves = $inbox['counts']['leaves'] ?? 0;
    $unreadChats = $inbox['counts']['chats'] ?? 0;
    $notifyCount = (int) ($inbox['notification_count'] ?? 0);
    $messageCount = (int) ($inbox['message_count'] ?? 0);
    $nav = [
        'DASHBOARD' => [
            ['label' => 'Dashboard', 'route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'icon' => 'home'],
            ['label' => 'Overview', 'route' => 'admin.overview', 'match' => 'admin.overview', 'icon' => 'grid'],
            ['label' => 'Projects', 'route' => 'admin.works.index', 'match' => 'admin.works.*', 'icon' => 'folder', 'permission' => 'module.client-projects'],
        ],
        'APPLICATIONS' => [
            ['label' => 'Job Applications', 'route' => 'admin.module.index', 'params' => ['module' => 'applications'], 'match' => 'admin.module.*', 'module' => 'applications', 'icon' => 'file', 'badge' => $newApps],
            ['label' => 'Internships', 'route' => 'admin.module.index', 'params' => ['module' => 'internships'], 'match' => 'admin.module.*', 'module' => 'internships', 'icon' => 'grad'],
            ['label' => 'Candidates', 'route' => 'admin.module.index', 'params' => ['module' => 'candidates'], 'match' => 'admin.module.*', 'module' => 'candidates', 'icon' => 'users'],
            ['label' => 'Interviews', 'route' => 'admin.module.index', 'params' => ['module' => 'interviews'], 'match' => 'admin.module.*', 'module' => 'interviews', 'icon' => 'calendar'],
            ['label' => 'Job Openings', 'route' => 'admin.module.index', 'params' => ['module' => 'jobs'], 'match' => 'admin.module.*', 'module' => 'jobs', 'icon' => 'briefcase'],
            ['label' => 'Departments', 'route' => 'admin.module.index', 'params' => ['module' => 'departments'], 'match' => 'admin.module.*', 'module' => 'departments', 'icon' => 'building'],
        ],
        'CAREERS WEBSITE' => [
            ['label' => 'Career Page', 'route' => 'admin.career-page', 'match' => 'admin.career-page', 'icon' => 'globe'],
        ],
        'EMPLOYEES' => [
            ['label' => 'Employees', 'route' => 'admin.module.index', 'params' => ['module' => 'employees'], 'match' => 'admin.module.*', 'module' => 'employees', 'icon' => 'user'],
            ['label' => 'Roles & Permissions', 'route' => 'admin.roles.index', 'match' => 'admin.roles.*', 'icon' => 'shield', 'permission' => 'roles.manage'],
            ['label' => 'Attendance', 'route' => 'admin.attendance.index', 'match' => 'admin.attendance.*', 'icon' => 'clock', 'permission' => 'module.attendance'],
            ['label' => 'Leave Requests', 'route' => 'admin.leaves.index', 'match' => 'admin.leaves.*', 'icon' => 'plane', 'permission' => 'module.leaves', 'badge' => $pendingLeaves],
            ['label' => 'Payroll', 'route' => 'admin.payroll.index', 'match' => 'admin.payroll.*', 'icon' => 'wallet', 'permission' => 'module.payroll'],
            ['label' => 'Tasks', 'route' => 'admin.tasks.index', 'match' => 'admin.tasks.*', 'icon' => 'check', 'permission' => 'module.tasks'],
        ],
        'COMMUNICATION' => [
            ['label' => 'Contact Inquiries', 'route' => 'admin.contacts.index', 'match' => 'admin.contacts.*', 'icon' => 'mail', 'badge' => $newContacts, 'permission' => 'module.contacts'],
            ['label' => 'Project Requests', 'route' => 'admin.module.index', 'params' => ['module' => 'projects'], 'match' => 'admin.module.*', 'module' => 'projects', 'icon' => 'briefcase', 'badge' => $newProjects],
            ['label' => 'Announcements', 'route' => 'admin.announcements.index', 'match' => 'admin.announcements.*', 'icon' => 'megaphone', 'permission' => 'module.announcements'],
            ['label' => 'Live Chat', 'route' => 'admin.chat.index', 'match' => 'admin.chat.*', 'icon' => 'send', 'permission' => 'module.chat', 'badge' => $unreadChats],
        ],
        'REPORTS & ANALYTICS' => [
            ['label' => 'Reports', 'route' => 'admin.reports', 'match' => 'admin.reports', 'icon' => 'report'],
            ['label' => 'Analytics', 'route' => 'admin.analytics', 'match' => 'admin.analytics', 'icon' => 'chart'],
        ],
        'SETTINGS' => [
            ['label' => 'Settings', 'route' => 'admin.settings', 'match' => 'admin.settings*', 'icon' => 'settings', 'permission' => 'settings.view'],
        ],
    ];

    $icon = function (string $name) {
        $paths = [
            'home' => '<path d="M3 10.5 12 3l9 7.5V21a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1v-10.5z"/>',
            'grid' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
            'folder' => '<path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
            'file' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M8 13h8M8 17h6"/>',
            'grad' => '<path d="M22 10 12 4 2 10l10 6 10-6z"/><path d="M6 12v5c3 2 9 2 12 0v-5"/>',
            'users' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
            'calendar' => '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
            'briefcase' => '<rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>',
            'building' => '<path d="M4 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16"/><path d="M16 9h4v12H4"/><path d="M8 8h2M8 12h2M8 16h2M12 8h2M12 12h2M12 16h2"/>',
            'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>',
            'image' => '<rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-5-5L5 21"/>',
            'doc' => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/>',
            'quote' => '<path d="M3 21c3 0 7-1 7-8V5H2v8h5c0 5-2 7-4 8zm12 0c3 0 7-1 7-8V5h-8v8h5c0 5-2 7-4 8z"/>',
            'help' => '<circle cx="12" cy="12" r="9"/><path d="M9.1 9a3 3 0 1 1 4.4 2.7c-.8.4-1.5 1.1-1.5 2"/><path d="M12 17h.01"/>',
            'user' => '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
            'shield' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
            'plane' => '<path d="M10.2 20.5 21 10.8 13.2 3 2.5 13.8l4.1.7 1.4 4.7z"/><path d="m13.2 3 4.2 4.2"/>',
            'wallet' => '<rect x="2" y="6" width="20" height="14" rx="2"/><path d="M2 10h20"/><path d="M16 14h2"/>',
            'check' => '<path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>',
            'mail' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/>',
            'send' => '<path d="m22 2-7 20-4-9-9-4z"/><path d="M22 2 11 13"/>',
            'megaphone' => '<path d="m3 11 19-7v14L3 11z"/><path d="M11.6 16.8a3 3 0 1 1-5.2-3"/>',
            'ticket' => '<path d="M3 9a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v2a2 2 0 0 0 0 4v2a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-2a2 2 0 0 0 0-4z"/>',
            'report' => '<path d="M4 19V5M4 19h16"/><path d="m8 15 3-4 3 2 4-6"/>',
            'chart' => '<path d="M4 19V5M4 19h16"/><rect x="7" y="10" width="3" height="6"/><rect x="12" y="7" width="3" height="9"/><rect x="17" y="12" width="3" height="4"/>',
            'settings' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V20a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H4a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H10a1.7 1.7 0 0 0 1-1.5V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V10c.3.6.9 1 1.5 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/>',
            'plug' => '<path d="M9 7v4M15 7v4M7 11h10v2a5 5 0 0 1-10 0v-2zM12 18v3"/>',
            'db' => '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5"/><path d="M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
        ];
        return $paths[$name] ?? $paths['file'];
    };
@endphp
<body class="kk-admin">
<div class="kk-shell" id="kkShell">
    <aside class="kk-sidebar">
        <a href="{{ route('admin.dashboard') }}" class="kk-brand">
            <img class="kk-brand__logo" src="{{ asset('images/kk-logo.png') }}" alt="KK Digital Solution" width="40" height="40">
            <div class="kk-brand__text">
                <strong>KK Digital</strong>
                <span>Career Admin</span>
            </div>
        </a>

        <nav class="kk-nav">
            @foreach ($nav as $group => $links)
                @php
                    $visibleLinks = collect($links)->filter(function ($link) use ($u) {
                        $perm = $link['permission'] ?? \App\Support\AdminPermissions::forNavItem($link);
                        if ($perm && (! $u || ! $u->hasPermission($perm))) {
                            return false;
                        }
                        if (($link['module'] ?? null) === 'announcements' && ! \App\Support\SiteSettings::bool('feature_announcements')) {
                            return false;
                        }
                        if (($link['route'] ?? '') === 'admin.announcements.index' && ! \App\Support\SiteSettings::bool('feature_announcements')) {
                            return false;
                        }
                        return true;
                    })->values();
                @endphp
                @continue($visibleLinks->isEmpty())
                <p class="kk-nav__label">{{ $group }}</p>
                @foreach ($visibleLinks as $link)
                    @php
                        $isModule = isset($link['module']);
                        $active = $isModule
                            ? request()->routeIs('admin.module.*') && request()->route('module') === $link['module']
                            : request()->routeIs($link['match']);
                        $href = route($link['route'], $link['params'] ?? []);
                    @endphp
                    <a href="{{ $href }}" class="{{ $active ? 'active' : '' }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">{!! $icon($link['icon']) !!}</svg>
                        <span>{{ $link['label'] }}</span>
                        @if (!empty($link['badge']))
                            <em>{{ $link['badge'] }}</em>
                        @endif
                    </a>
                @endforeach
            @endforeach
        </nav>

        <div class="kk-sidebar__foot">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="kk-logout" type="submit">Sign out</button>
            </form>
        </div>
    </aside>

    <div class="kk-main">
        <header class="kk-topbar">
            <button class="kk-icon-btn" type="button" id="kkMenuBtn" aria-label="Toggle menu">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <form class="kk-search" action="{{ route('admin.module.index', 'applications') }}" method="GET">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <input type="search" name="q" placeholder="Search anything..." value="{{ request('q') }}">
                <kbd>Ctrl + /</kbd>
            </form>
            <div class="kk-topbar__right">
                <div class="kk-drop" data-drop>
                    <button class="kk-icon-btn" type="button" data-drop-btn title="Notifications" aria-label="Notifications">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10 21a2 2 0 0 0 4 0"/></svg>
                        @if ($notifyCount > 0)
                            <span class="kk-badge" data-badge="notifications">{{ min(99, $notifyCount) }}</span>
                        @else
                            <span class="kk-badge" data-badge="notifications" hidden>0</span>
                        @endif
                    </button>
                    <div class="kk-drop__panel" data-drop-panel hidden>
                        <div class="kk-drop__h">
                            <strong>Notifications</strong>
                            <a href="{{ route('admin.notifications.index') }}">See all</a>
                        </div>
                        @forelse ($inbox['notifications'] as $item)
                            <a class="kk-drop__item {{ !empty($item['unread']) ? 'is-unread' : '' }}" href="{{ $item['url'] }}">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['body'] }}</span>
                                <em>{{ $item['at'] ? \App\Support\AppTime::formatShort($item['at']) : '' }}</em>
                            </a>
                        @empty
                            <p class="kk-drop__empty">No new notifications.</p>
                        @endforelse
                    </div>
                </div>
                <div class="kk-drop" data-drop>
                    <button class="kk-icon-btn" type="button" data-drop-btn title="Messages" aria-label="Messages">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/></svg>
                        @if ($messageCount > 0)
                            <span class="kk-badge" data-badge="messages">{{ min(99, $messageCount) }}</span>
                        @else
                            <span class="kk-badge" data-badge="messages" hidden>0</span>
                        @endif
                    </button>
                    <div class="kk-drop__panel" data-drop-panel hidden>
                        <div class="kk-drop__h">
                            <strong>Messages</strong>
                            <a href="{{ route('admin.messages.index') }}">See all</a>
                        </div>
                        @forelse ($inbox['messages'] as $item)
                            <a class="kk-drop__item {{ !empty($item['unread']) ? 'is-unread' : '' }}" href="{{ $item['url'] }}">
                                <strong>{{ $item['title'] }}</strong>
                                <span>{{ $item['body'] }}</span>
                                <em>{{ $item['at'] ? \App\Support\AppTime::formatShort($item['at']) : '' }}</em>
                            </a>
                        @empty
                            <p class="kk-drop__empty">No new messages.</p>
                        @endforelse
                    </div>
                </div>
                <a class="kk-icon-btn" href="{{ route('admin.settings') }}" title="Settings">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.8-.3 1.7 1.7 0 0 0-1 1.5V20a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.5 1.7 1.7 0 0 0-1.8.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.8 1.7 1.7 0 0 0-1.5-1H4a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.5-1 1.7 1.7 0 0 0-.3-1.8l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.8.3H10a1.7 1.7 0 0 0 1-1.5V4a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5 1.7 1.7 0 0 0 1.8-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.8V10c.3.6.9 1 1.5 1H20a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1z"/></svg>
                </a>
                <div class="kk-drop" data-drop>
                    <button class="kk-profile" type="button" data-drop-btn aria-haspopup="menu" aria-label="Account menu">
                        @if ($u?->avatarUrl())
                            <img class="kk-profile__avatar" src="{{ $u->avatarUrl() }}" alt="">
                        @else
                            <span class="kk-profile__avatar">{{ $u?->initials() ?? 'A' }}</span>
                        @endif
                        <span class="kk-profile__meta">
                            <strong>{{ $u?->name ?: 'Admin' }}</strong>
                            <span>{{ $u?->adminRole?->name ?? 'Admin' }}</span>
                        </span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="kk-drop__panel kk-profile-panel" data-drop-panel hidden>
                        <div class="kk-profile-panel__head">
                            @if ($u?->avatarUrl())
                                <img class="kk-profile__avatar" src="{{ $u->avatarUrl() }}" alt="">
                            @else
                                <span class="kk-profile__avatar">{{ $u?->initials() ?? 'A' }}</span>
                            @endif
                            <div>
                                <strong>{{ $u?->name ?: 'Admin' }}</strong>
                                <span>{{ $u?->email }}</span>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.profile.avatar') }}" enctype="multipart/form-data">
                            @csrf
                            <label class="kk-drop__item kk-drop__item--btn">
                                Change photo
                                <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp,.jpg,.jpeg,.png,.webp" hidden onchange="this.form.submit()">
                            </label>
                        </form>
                        <a class="kk-drop__item" href="{{ route('admin.profile.edit') }}">My profile</a>
                        @if (! $u || $u->hasPermission('settings.view'))
                            <a class="kk-drop__item" href="{{ route('admin.settings') }}">Settings</a>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="kk-drop__item kk-drop__item--btn is-danger" type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="kk-content">
            @if (session('success'))
                <div class="kk-flash">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="kk-flash kk-flash--error">{{ $errors->first() }}</div>
            @endif
            @yield('content')
        </div>

        <footer class="kk-footer">
            <span>© {{ date('Y') }} KK Digital Technologies Pvt. Ltd. All rights reserved.</span>
            <span>Version 1.0.0</span>
        </footer>
    </div>
</div>
<script>
(() => {
  const shell = document.getElementById('kkShell');
  const btn = document.getElementById('kkMenuBtn');
  btn?.addEventListener('click', () => {
    if (window.innerWidth <= 980) shell.classList.toggle('is-sidebar-open');
    else shell.classList.toggle('is-collapsed');
  });
  document.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
      e.preventDefault();
      document.querySelector('.kk-search input')?.focus();
    }
  });

  const drops = [...document.querySelectorAll('[data-drop]')];
  const closeDrops = () => drops.forEach((el) => {
    const panel = el.querySelector('[data-drop-panel]');
    if (panel) panel.hidden = true;
  });
  drops.forEach((wrap) => {
    const trigger = wrap.querySelector('[data-drop-btn]');
    const panel = wrap.querySelector('[data-drop-panel]');
    trigger?.addEventListener('click', (e) => {
      e.stopPropagation();
      const open = panel && !panel.hidden;
      closeDrops();
      if (panel && !open) panel.hidden = false;
    });
    panel?.addEventListener('click', (e) => e.stopPropagation());
  });
  document.addEventListener('click', closeDrops);

  const summaryUrl = @json(route('admin.inbox.summary'));
  const paintBadge = (key, n) => {
    const el = document.querySelector('[data-badge="'+key+'"]');
    if (!el) return;
    const count = Number(n) || 0;
    if (count > 0) {
      el.hidden = false;
      el.textContent = count > 99 ? '99' : String(count);
    } else {
      el.hidden = true;
    }
  };
  const refreshBadges = async () => {
    try {
      const res = await fetch(summaryUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
      if (!res.ok) return;
      const data = await res.json();
      paintBadge('notifications', data.notifications);
      paintBadge('messages', data.messages);
    } catch (e) {}
  };
  setInterval(refreshBadges, 20000);
})();
</script>
    <script src="{{ asset('js/kk-ajax.js') }}"></script>
    <script src="{{ asset('js/kk-live.js') }}"></script>
@stack('scripts')
</body>
</html>
