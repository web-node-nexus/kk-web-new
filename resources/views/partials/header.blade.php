@php
    $nav = [
        ['route' => 'home', 'label' => 'Home'],
        ['route' => 'about', 'label' => 'About Us'],
        ['route' => 'case-studies', 'label' => 'Portfolio'],
        ['route' => 'careers', 'label' => 'Careers'],
        ['route' => 'contact', 'label' => 'Contact Us'],
    ];
@endphp
<header class="tn-header">
    <div class="container-tn flex items-center justify-between gap-4 py-3.5">
        <a href="{{ route('home') }}" class="tn-logo tn-logo--header" aria-label="KK Digital Solution">
            <img src="{{ asset('images/kk-logo.png') }}" alt="KK Digital Solution" width="48" height="48">
        </a>

        <nav class="tn-nav hidden items-center gap-5 xl:flex">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">{{ $item['label'] }}</a>
            @endforeach
        </nav>

        <div class="flex items-center gap-2 sm:gap-3">
            @auth
                <a href="{{ route('admin.dashboard') }}" class="hidden text-sm font-semibold text-[#374151] hover:text-[#2f80ed] md:inline">Admin</a>
            @else
                <a href="{{ route('login') }}" class="hidden text-sm font-semibold text-[#374151] hover:text-[#2f80ed] md:inline">Login</a>
            @endauth
            <a href="{{ route('start-project') }}" class="tn-btn tn-btn-primary !py-2.5 !px-4 text-sm hidden sm:inline-flex">
                Get a Quote
                <span class="tn-btn-arrow">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                </span>
            </a>
            <button id="nav-toggle" type="button" class="xl:hidden rounded-full border border-slate-200 px-3 py-2 text-sm font-semibold" aria-label="Menu">Menu</button>
        </div>
    </div>

    <div id="mobile-nav" class="hidden border-t border-slate-100 px-4 py-4 xl:hidden">
        <div class="flex flex-col gap-1">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold hover:bg-slate-50 {{ request()->routeIs($item['route']) ? 'text-[#2f80ed]' : '' }}">{{ $item['label'] }}</a>
            @endforeach
            @auth
                <a href="{{ route('admin.dashboard') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold hover:bg-slate-50">Admin</a>
            @else
                <a href="{{ route('login') }}" class="rounded-lg px-3 py-2.5 text-sm font-semibold hover:bg-slate-50">Login</a>
            @endauth
            <a href="{{ route('start-project') }}" class="tn-btn tn-btn-primary mt-3 text-center text-sm">Get a Quote</a>
        </div>
    </div>
</header>
