@extends('layouts.app')
@section('title', 'About Us | K&K')
@section('content')
@include('partials.page-hero', [
    'title' => 'About K&K',
    'subtitle' => 'A modern technology firm helping organizations transform, grow and lead with secure, scalable digital systems.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'About Us']],
])

<section class="tn-section">
    <div class="container-tn tn-split">
        <div>
            <p class="tn-eyebrow">Our Story</p>
            <h2 class="mt-3 text-3xl font-bold">Build. Scale. Grow.</h2>
            <p class="mt-4 text-[#6b7280] leading-relaxed">K&K was founded to close the gap between strategy decks and shipped software. Today we partner with startups and enterprises across web, mobile, cloud, cybersecurity, consulting and AI.</p>
            <ul class="tn-list-check">
                <li>Named delivery ownership on every engagement</li>
                <li>Security and quality gates as standard practice</li>
                <li>Global delivery with local accountability</li>
            </ul>
        </div>
        <div class="tn-media aspect-[4/3]">
            <img src="{{ asset('images/generated/about-team.png') }}" alt="K&K team collaboration">
        </div>
    </div>
</section>

<section class="tn-section tn-soft-bg">
    <div class="container-tn">
        <div class="tn-section-head">
            <p class="tn-eyebrow">Leadership</p>
            <h2>Our <span class="accent">Founders</span></h2>
            <p>Experienced leaders driving strategy, delivery and long-term client success.</p>
        </div>

        <div class="tn-founder-grid">
            @foreach ($founders as $founder)
                @php
                    $initials = collect(explode(' ', $founder->name))->map(fn ($p) => mb_substr($p, 0, 1))->join('');
                    $phone = $founder->phone ? preg_replace('/\s+/', '', $founder->phone) : null;
                @endphp
                <article class="tn-founder-card">
                    <div class="tn-founder-card__media">
                        @if ($founder->photo)
                            <img src="{{ asset($founder->photo) }}" alt="{{ $founder->name }}" class="tn-founder-card__photo">
                        @else
                            <div class="tn-founder-card__placeholder" aria-hidden="true">{{ $initials }}</div>
                        @endif
                    </div>
                    <div class="tn-founder-card__body">
                        <p class="tn-founder-card__badge">Founder</p>
                        <h3>{{ $founder->name }}</h3>
                        <p class="tn-founder-card__bio">{{ $founder->bio }}</p>
                        @if ($phone)
                            <a class="tn-founder-card__phone" href="tel:+91{{ $phone }}">
                                <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M6.6 3.8h2.1c.5 0 .9.3 1 .8l.7 2.7c.1.4 0 .8-.3 1.1l-1.3 1.3a12.1 12.1 0 0 0 5.5 5.5l1.3-1.3c.3-.3.7-.4 1.1-.3l2.7.7c.5.1.8.5.8 1v2.1c0 .6-.5 1.1-1.1 1.1A14.2 14.2 0 0 1 5.5 4.9c0-.6.5-1.1 1.1-1.1Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                                </svg>
                                <span>+91 {{ trim(chunk_split($phone, 5, ' ')) }}</span>
                            </a>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="tn-section">
    <div class="container-tn">
        <div class="tn-section-head">
            <p class="tn-eyebrow">Our Team</p>
            <h2>People who make it <span class="accent">happen</span></h2>
            <p>Project, marketing and internship talent working together on every engagement.</p>
        </div>

        <div class="tn-team-grid">
            @foreach ($team as $member)
                @php
                    $initials = collect(explode(' ', $member->name))->map(fn ($p) => mb_substr($p, 0, 1))->join('');
                @endphp
                <article class="tn-team-card">
                    <div class="tn-team-card__media">
                        @if ($member->photo)
                            <img src="{{ asset($member->photo) }}" alt="{{ $member->name }}" class="tn-team-card__photo">
                        @else
                            <div class="tn-team-card__avatar" aria-hidden="true">{{ $initials }}</div>
                        @endif
                    </div>
                    <div class="tn-team-card__body">
                        <h3>{{ $member->name }}</h3>
                        <p class="tn-team-card__role">{{ $member->role }}</p>
                        <p class="tn-team-card__bio">{{ $member->bio }}</p>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="tn-section tn-soft-bg">
    <div class="container-tn">
        <div class="tn-section-head">
            <p class="tn-eyebrow">Principles</p>
            <h2>How we work as a <span class="accent">partner</span></h2>
        </div>
        <div class="tn-feature-grid cols-3">
            @foreach ([
                ['Customer Obsession', 'We measure success by outcomes for your customers and your business.', 'icon-clients.png'],
                ['Integrity', 'Honest timelines, transparent pricing and clear trade-offs.', 'icon-security.png'],
                ['Excellence', 'Craftsmanship in design, code, architecture and operations.', 'icon-star.png'],
                ['Collaboration', 'We operate as an extension of your team — not a black box.', 'icon-people.png'],
                ['Innovation', 'Modern stacks chosen for performance today and scale tomorrow.', 'icon-ai.png'],
                ['Accountability', 'Clear owners, visible progress and measurable delivery.', 'icon-briefcase.png'],
            ] as $v)
                <article class="tn-card p-6">
                    <div class="tn-icon-tile">
                        <img src="{{ asset('images/icons/'.$v[2]) }}" alt="">
                    </div>
                    <h3 class="m-0 text-lg font-bold">{{ $v[0] }}</h3>
                    <p class="mt-2 mb-0 text-sm text-[#6b7280]">{{ $v[1] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

@include('partials.cta-band')
@endsection
