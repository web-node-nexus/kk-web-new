@extends('layouts.app')

@section('title', 'Dashboard | K&K')

@section('content')
<section class="tn-page-hero">
    <div class="container-tn">
        <p class="tn-eyebrow">Admin Portal</p>
        <h1>Welcome, {{ $user->name }}</h1>
        <p>Review job applications and manage your K&K workspace from one place.</p>
    </div>
</section>

<section class="tn-section">
    <div class="container-tn">
        <div class="tn-feature-grid cols-3 mb-8">
            <article class="tn-card p-7">
                <div class="tn-icon-tile">
                    <img src="{{ asset('images/icons/icon-people.png') }}" alt="">
                </div>
                <h2 class="text-xl font-bold m-0">Job Applications</h2>
                <p class="mt-2 mb-0 text-sm text-[#6b7280]">{{ $applications->count() }} latest submissions shown below.</p>
            </article>
            <article class="tn-card p-7">
                <div class="tn-icon-tile">
                    <img src="{{ asset('images/icons/icon-briefcase.png') }}" alt="">
                </div>
                <h2 class="text-xl font-bold m-0">New Applications</h2>
                <p class="mt-2 mb-0 text-sm text-[#6b7280]">{{ $applications->where('status', 'new')->count() }} marked as new.</p>
            </article>
            <article class="tn-card p-7">
                <div class="tn-icon-tile">
                    <img src="{{ asset('images/icons/icon-consulting.png') }}" alt="">
                </div>
                <h2 class="text-xl font-bold m-0">Account</h2>
                <p class="mt-2 mb-0 text-sm text-[#6b7280]">Signed in as {{ $user->email }}</p>
            </article>
        </div>

        <div class="tn-card overflow-hidden">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="m-0 text-xl font-bold">Submitted Job Applications</h2>
                <p class="mt-1 mb-0 text-sm text-[#6b7280]">Candidates who applied via Careers → Apply Now.</p>
            </div>

            @forelse ($applications as $app)
                <article class="tn-admin-app">
                    <div class="tn-admin-app__top">
                        <div>
                            <p class="tn-admin-app__badge">{{ strtoupper($app->status) }}</p>
                            <h3>{{ $app->full_name }}</h3>
                            <p class="tn-admin-app__meta">
                                Applied for <strong>{{ $app->position }}</strong>
                                · {{ $app->department }}
                                · {{ $app->job_type }}
                            </p>
                        </div>
                        <p class="tn-admin-app__date">{{ $app->created_at?->format('d M Y, h:i A') }}</p>
                    </div>

                    <div class="tn-admin-app__grid">
                        <p><span>Email</span><a href="mailto:{{ $app->email }}">{{ $app->email }}</a></p>
                        <p><span>Phone</span><a href="tel:{{ preg_replace('/\s+/', '', (string) $app->phone) }}">{{ $app->phone }}</a></p>
                        <p><span>Qualification</span>{{ $app->qualification ?: '—' }}</p>
                        <p><span>Experience</span>{{ $app->total_experience ?: '—' }}</p>
                        <p><span>University</span>{{ $app->university ?: '—' }}</p>
                        <p><span>Source</span>{{ $app->source ?: '—' }}</p>
                    </div>

                    @if ($app->why_join)
                        <p class="tn-admin-app__note"><span>Why join</span>{{ $app->why_join }}</p>
                    @endif

                    <div class="tn-admin-app__files">
                        @if ($app->resume_path)
                            <a class="tn-btn tn-btn-primary !py-2 !px-4 text-sm" href="{{ asset('storage/'.$app->resume_path) }}" target="_blank" rel="noopener">Download Resume</a>
                        @endif
                        @if ($app->cover_letter_path)
                            <a class="tn-btn tn-btn-outline !py-2 !px-4 text-sm" href="{{ asset('storage/'.$app->cover_letter_path) }}" target="_blank" rel="noopener">Cover Letter</a>
                        @endif
                    </div>
                </article>
            @empty
                <div class="px-6 py-10 text-center text-sm text-[#6b7280]">
                    No job applications yet. When candidates submit the Apply Now form, they will appear here.
                </div>
            @endforelse
        </div>

        <div class="tn-card mt-8 flex flex-col gap-4 p-7 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="m-0 text-xl font-bold">Sign out</h2>
                <p class="mt-1 mb-0 text-sm text-[#6b7280]">Leave the admin workspace securely.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="tn-btn tn-btn-outline">Sign out</button>
            </form>
        </div>
    </div>
</section>
@endsection
