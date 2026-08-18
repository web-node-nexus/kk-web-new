@extends('layouts.app')
@section('title', 'Why K&K | K&K')
@section('content')
@include('partials.page-hero', [
    'title' => 'Why Organizations Choose K&K',
    'subtitle' => 'Consulting clarity, engineering depth and post-launch ownership — the operating model modern enterprises expect.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Why Us']],
])

<section class="tn-section">
    <div class="container-tn">
        <div class="tn-feature-grid cols-3">
            @foreach ([
                ['01','Product thinking','We optimize for adoption, retention and business impact — not ticket volume.','icon-star.png'],
                ['02','Modern technology','Cloud-native, secure stacks chosen for performance today and scale tomorrow.','icon-cloud.png'],
                ['03','Transparent delivery','Named owners, visible sprints and stakeholder-ready reporting.','icon-consulting.png'],
                ['04','Dedicated experts','Cross-functional squads who have shipped across industries and stages.','icon-people.png'],
                ['05','Measured outcomes','Instrumentation and KPIs baked into every release cycle.','icon-briefcase.png'],
                ['06','Security by default','Architecture reviews, secure coding and QA as standard practice.','icon-security.png'],
            ] as $item)
                <article class="tn-card p-7">
                    <div class="tn-icon-tile">
                        <img src="{{ asset('images/icons/'.$item[3]) }}" alt="">
                    </div>
                    <p class="m-0 text-sm font-bold text-[#2f80ed]">{{ $item[0] }}</p>
                    <h2 class="mt-2 mb-2 text-xl font-bold">{{ $item[1] }}</h2>
                    <p class="m-0 text-sm leading-relaxed text-[#6b7280]">{{ $item[2] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="tn-section tn-soft-bg">
    <div class="container-tn tn-split">
        <div class="tn-media aspect-[4/3]">
            <img src="{{ asset('images/generated/about-team.png') }}" alt="K&K delivery team">
        </div>
        <div>
            <p class="tn-eyebrow">Proof Points</p>
            <h2 class="mt-3 text-3xl font-bold">Trusted delivery at scale</h2>
            <ul class="tn-list-check">
                <li>100+ happy clients across startups and enterprises</li>
                <li>70+ projects delivered with measurable outcomes</li>
                <li>30+ expert professionals across engineering and consulting</li>
                <li>10+ countries served with global collaboration models</li>
            </ul>
        </div>
    </div>
</section>
@include('partials.cta-band')
@endsection
