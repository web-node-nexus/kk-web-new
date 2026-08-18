@extends('layouts.app')
@section('title', 'Portfolio | KK Digital Solution')
@section('content')
@include('partials.page-hero', [
    'title' => 'Our Portfolio',
    'subtitle' => 'Live products we designed and built — open any project in your browser, or watch the demo where available.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Portfolio']],
])

<section class="tn-section">
    <div class="container-tn">
        <div class="tn-section-head">
            <p class="tn-eyebrow">Selected Work</p>
            <h2>Projects that are <span class="accent">live</span></h2>
            <p>Each card links to the live product. Demo recordings play inside the card where available.</p>
        </div>

        @if (! $activeFolder)
            <div class="tn-folder-grid">
                @foreach ($folders as $folder)
                    @php $count = (int) ($folderCounts[$folder['key']] ?? 0); @endphp
                    <a
                        href="{{ route('case-studies', ['folder' => $folder['key']]) }}#projects"
                        class="tn-folder tn-folder--hero"
                    >
                        <span class="tn-folder__icon" aria-hidden="true">
                            <img src="{{ asset($folder['icon']) }}" alt="">
                        </span>
                        <span class="tn-folder__meta">
                            <strong>{{ $folder['label'] }}</strong>
                            <em>{{ $folder['hint'] }}</em>
                            <small>{{ $count }} {{ $count === 1 ? 'project' : 'projects' }}</small>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif

        <div id="projects">
            @if ($activeFolder)
                @php
                    $activeLabel = collect($folders)->firstWhere('key', $activeFolder)['label'] ?? 'Projects';
                @endphp

                <div class="tn-folder-open-label">
                    <a href="{{ route('case-studies') }}" class="tn-folder-back">Back to folders</a>
                    <span>Open folder</span>
                    <h3>{{ $activeLabel }}</h3>
                </div>

                @if ($cases->isEmpty())
                    <div class="tn-folder-empty">
                        <strong>{{ $activeLabel }}</strong>
                        <p>Projects in this folder will appear here soon.</p>
                    </div>
                @else
                    <div class="tn-portfolio-grid">
                        @foreach ($cases as $case)
                            @php
                                $host = $case->project_url ? preg_replace('/^www\./', '', parse_url($case->project_url, PHP_URL_HOST) ?? '') : '';
                            @endphp
                            <article class="tn-portfolio-card">
                                <div class="tn-portfolio-card__media">
                                    @if ($case->video)
                                        <video
                                            class="tn-portfolio-card__video"
                                            src="{{ asset($case->video) }}"
                                            muted
                                            loop
                                            playsinline
                                            preload="metadata"
                                            controls
                                        ></video>
                                    @else
                                        <div class="tn-portfolio-card__placeholder">
                                            <span>{{ collect(explode(' ', $case->title))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->join('') }}</span>
                                            <p>{{ $case->title }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="tn-portfolio-card__body">
                                    <p class="tn-portfolio-card__tag">{{ $case->industry }}</p>
                                    <h3>{{ $case->title }}</h3>
                                    <p class="tn-portfolio-card__summary">{{ $case->summary }}</p>

                                    @if ($case->project_url)
                                        <a
                                            class="tn-portfolio-card__link"
                                            href="{{ $case->project_url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            <span>{{ $host ?: $case->project_url }}</span>
                                            <svg viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M7 17L17 7M17 7H9M17 7v8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </a>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                @endif
            @else
                <p class="tn-folder-hint">Open a folder above to view live projects.</p>
            @endif
        </div>
    </div>
</section>

<section class="tn-section tn-soft-bg">
    <div class="container-tn text-center">
        <h2 class="text-3xl font-bold">Have a similar challenge?</h2>
        <p class="mx-auto mt-3 max-w-xl text-[#6b7280]">Share your goals and we will map a delivery approach with clear milestones and commercial options.</p>
        <a href="{{ route('start-project') }}" class="tn-btn tn-btn-primary mt-7">Start a project conversation</a>
    </div>
</section>
@endsection
