@extends('layouts.app')

@section('title', 'K&K | Build. Scale. Grow.')
@section('description', 'K&K delivers innovative, scalable and secure IT solutions that help businesses transform, grow and lead in the digital era.')

@section('content')
<section class="tn-hero">
    <div class="container-tn">
        <div class="tn-hero-grid">
            <div>
                <h1>Technology <span class="accent">Solutions</span> for a Better Tomorrow</h1>
                <p class="tn-hero-lead">
                    We deliver innovative, scalable and secure IT solutions that help businesses transform, grow and lead in the digital era.
                </p>
                <div class="tn-hero-actions">
                    <a href="{{ route('start-project') }}" class="tn-btn tn-btn-primary">
                        Get a Quote
                        <span class="tn-btn-arrow">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                    <a href="{{ route('contact') }}" class="tn-btn tn-btn-outline">
                        Contact Us
                        <span class="tn-btn-arrow">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12h14M13 5l7 7-7 7"/></svg>
                        </span>
                    </a>
                </div>
            </div>

            <div class="tn-hero-visual">
                <div class="tn-hero-frame">
                    <video
                        id="hero-circle-video"
                        src="{{ asset('videos/hero-circle.mp4') }}"
                        autoplay
                        loop
                        playsinline
                        preload="auto"
                        disablepictureinpicture
                        controlslist="nodownload nofullscreen noremoteplayback"
                    ></video>
                </div>

                <div class="tn-float-card tn-float-card--1">
                    <div class="tn-float-card__icon">
                        <img src="{{ asset('images/icons/icon-people.png') }}" alt="">
                    </div>
                    <div>
                        <strong>10+</strong>
                        <span>Years of Excellence</span>
                    </div>
                </div>

                <div class="tn-float-card tn-float-card--2">
                    <div class="tn-float-card__icon">
                        <img src="{{ asset('images/icons/icon-rocket.png') }}" alt="">
                    </div>
                    <div>
                        <strong>70+</strong>
                        <span>Projects Delivered</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="tn-section">
    <div class="container-tn">
        <div class="tn-section-head">
            <p class="tn-eyebrow">What We Do</p>
            <h2>Our <span class="accent">Services</span></h2>
            <p>We provide end-to-end technology solutions tailored to your business needs. From strategy to execution, we ensure quality and innovation.</p>
        </div>

        <div class="tn-services-grid">
            @foreach ([
                ['Web Development', 'Modern, fast and scalable websites and web applications built with the latest technologies.', 'icon-web.png'],
                ['Mobile App Development', 'Native and cross-platform mobile apps that deliver seamless user experiences.', 'icon-mobile.png'],
                ['Cloud Solutions', 'Secure cloud architecture, migration and managed services for modern businesses.', 'icon-cloud.png'],
                ['Cyber Security', 'Protect your digital assets with enterprise-grade security assessments and defenses.', 'icon-security.png'],
                ['IT Consulting', 'Strategic technology consulting to align IT investments with business growth.', 'icon-consulting.png'],
            ] as $service)
                <article class="tn-service-card">
                    <div class="tn-service-card__icon">
                        <img src="{{ asset('images/icons/'.$service[2]) }}" alt="{{ $service[0] }}">
                    </div>
                    <h3>{{ $service[0] }}</h3>
                    <p>{{ $service[1] }}</p>
                    <a href="{{ route('start-project') }}" class="more">Get a Quote →</a>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="tn-stats">
    <div class="container-tn">
        <div class="tn-stats-grid">
            @foreach ([
                ['100+', 'Happy Clients', 'icon-clients.png'],
                ['70+', 'Projects Delivered', 'icon-briefcase.png'],
                ['30+', 'Expert Professionals', 'icon-people.png'],
                ['10+', 'Countries Served', 'icon-globe.png'],
            ] as $stat)
                <div class="tn-stat">
                    <div class="tn-stat__icon">
                        <img src="{{ asset('images/icons/'.$stat[2]) }}" alt="">
                    </div>
                    <div>
                        <strong>{{ $stat[0] }}</strong>
                        <span>{{ $stat[1] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endsection
