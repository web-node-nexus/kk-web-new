@extends('layouts.employee')
@section('title', 'Interview Details')
@section('heading', 'Interview Details')
@section('subheading', 'Full candidate information for your assigned interview.')

@section('content')
@php
    $app = $application;
    $when = optional($interview->scheduled_at)->timezone(config('app.timezone', 'Asia/Kolkata'));
@endphp

<div style="margin-bottom:14px">
    <a class="ep-btn ep-btn-ghost" href="{{ route('employee.interviews') }}">← Back to list</a>
</div>

<section class="ep-hero">
    <div class="ep-hero__eyebrow">Assigned interview</div>
    <h2>{{ $interview->candidate_name }}</h2>
    <p>
        {{ $interview->position ?: 'Role TBD' }}
        · {{ $when ? $when->format('l, d F Y · h:i A') : 'Time TBD' }} (IST)
        · {{ $interview->mode ?: 'Online' }}
    </p>
    <div class="ep-hero__actions">
        @if ($interview->meeting_link)
            <a class="ep-btn ep-btn-primary" href="{{ $interview->meeting_link }}" target="_blank" rel="noopener">Join meeting</a>
        @endif
        @if (strtolower((string) $interview->status) !== 'completed')
            <form method="POST" action="{{ route('employee.interviews.complete', $interview->id) }}">
                @csrf
                <button class="ep-btn ep-btn-navy" type="submit">Mark completed</button>
            </form>
        @else
            <span class="ep-btn ep-btn-ghost" style="cursor:default;background:rgba(255,255,255,.12);color:#fff;border:0">Completed</span>
        @endif
    </div>
</section>

<div class="ep-grid-2">
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Schedule</h3></div>
        <div class="ep-card__b">
            <div class="ep-kv">
                <span>Date & time</span><strong>{{ $when ? $when->format('d M Y, h:i A') : '—' }}</strong>
                <span>Mode</span><strong>{{ $interview->mode ?: '—' }}</strong>
                <span>Meeting link</span>
                <strong>
                    @if ($interview->meeting_link)
                        <a href="{{ $interview->meeting_link }}" target="_blank" rel="noopener" style="color:#0d9488;word-break:break-all">{{ $interview->meeting_link }}</a>
                    @else
                        —
                    @endif
                </strong>
                <span>Status</span><strong>{{ $interview->status }}</strong>
                <span>Interviewer</span><strong>{{ $interview->interviewer ?: $employee->name }}</strong>
            </div>
        </div>
    </div>

    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Contact</h3></div>
        <div class="ep-card__b">
            <div class="ep-kv">
                <span>Name</span><strong>{{ $interview->candidate_name }}</strong>
                <span>Email</span><strong>{{ $app->email ?? '—' }}</strong>
                <span>Phone</span><strong>{{ $app->phone ?? '—' }}</strong>
                <span>Position</span><strong>{{ $interview->position ?: ($app->position ?? '—') }}</strong>
                <span>Department</span><strong>{{ $app->department ?? '—' }}</strong>
                <span>Job type</span><strong>{{ $app->job_type ?? '—' }}</strong>
            </div>
        </div>
    </div>
</div>

@if ($app)
<div class="ep-card">
    <div class="ep-card__h"><h3>Full candidate profile</h3></div>
    <div class="ep-card__b">
        <div class="ep-kv">
            <span>Qualification</span><strong>{{ $app->qualification ?: '—' }}</strong>
            <span>University</span><strong>{{ $app->university ?: '—' }}</strong>
            <span>Passing year</span><strong>{{ $app->passing_year ?: '—' }}</strong>
            <span>Field of study</span><strong>{{ $app->field_of_study ?: '—' }}</strong>
            <span>CGPA / %</span><strong>{{ $app->percentage_cgpa ?: '—' }}</strong>
            <span>Experience</span><strong>{{ $app->total_experience ?: '—' }}</strong>
            <span>Last company</span><strong>{{ $app->last_company ?: '—' }}</strong>
            <span>Last job title</span><strong>{{ $app->job_title ?: '—' }}</strong>
            <span>Gender</span><strong>{{ $app->gender ?: '—' }}</strong>
            <span>Nationality</span><strong>{{ $app->nationality ?: '—' }}</strong>
            <span>Address</span><strong>{{ $app->address ?: '—' }}</strong>
            <span>Source</span><strong>{{ $app->source ?: '—' }}</strong>
        </div>

        @if ($app->responsibilities)
            <div style="margin-top:16px">
                <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">Responsibilities</div>
                <div style="font-size:14px;line-height:1.65;white-space:pre-wrap">{{ $app->responsibilities }}</div>
            </div>
        @endif
        @if ($app->why_join)
            <div style="margin-top:16px">
                <div style="font-size:12px;font-weight:700;color:#64748b;margin-bottom:6px">Why join us</div>
                <div style="font-size:14px;line-height:1.65;white-space:pre-wrap">{{ $app->why_join }}</div>
            </div>
        @endif

        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:18px">
            @if ($app->resume_path)
                <a class="ep-btn ep-btn-primary" href="{{ asset('storage/'.$app->resume_path) }}" target="_blank" rel="noopener">Download resume</a>
            @endif
            @if ($app->cover_letter_path)
                <a class="ep-btn ep-btn-ghost" href="{{ asset('storage/'.$app->cover_letter_path) }}" target="_blank" rel="noopener">Cover letter</a>
            @endif
        </div>
    </div>
</div>
@endif

@if ($interview->notes)
<div class="ep-card">
    <div class="ep-card__h"><h3>Admin / HR notes</h3></div>
    <div class="ep-card__b" style="white-space:pre-wrap;font-size:14px;line-height:1.65">{{ $interview->notes }}</div>
</div>
@endif
@endsection
