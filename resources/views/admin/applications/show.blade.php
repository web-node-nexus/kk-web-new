@extends('layouts.admin')

@section('title', $item->full_name.' | Job Application')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $item->full_name }}</h1>
        <p>Applied for <strong>{{ $item->position }}</strong> · {{ $item->created_at?->diffForHumans() }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.module.index', 'applications') }}" class="kk-btn kk-btn-secondary">Back to list</a>
    </div>
</div>

{{-- Pipeline --}}
<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__body">
        <p style="margin:0 0 12px;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#94a3b8">Hiring pipeline</p>
        <div class="kk-pipeline">
            @foreach ($pipeline as $key => $label)
                @php
                    $order = array_keys($pipeline);
                    $current = array_search($item->status, $order, true);
                    $step = array_search($key, $order, true);
                    $done = ! in_array($item->status, ['rejected', 'internship_offered'], true) && $current !== false && $step <= $current;
                    $active = $item->status === $key;
                    $isReject = $key === 'rejected';
                    $isInternOffer = $key === 'internship_offered';
                @endphp
                <div class="kk-pipeline__step {{ $active ? 'is-active' : '' }} {{ $done && !$isReject && !$isInternOffer ? 'is-done' : '' }} {{ $isReject && $active ? 'is-reject' : '' }} {{ $isInternOffer && $active ? 'is-intern' : '' }}">
                    <span>{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- Quick decisions --}}
<section class="kk-card" style="margin-bottom:16px">
    <div class="kk-card__head"><h2>Take action</h2></div>
    <div class="kk-card__body">
        <div class="kk-decision">
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="reviewed">
                <button class="kk-btn kk-btn-secondary" type="submit">Mark Reviewed</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="shortlisted">
                <button class="kk-btn kk-btn-secondary" type="submit" style="border-color:#bbf7d0;color:#15803d;background:#f0fdf4">Shortlist</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="interview">
                <button class="kk-btn kk-btn-secondary" type="submit" style="border-color:#ddd6fe;color:#6d28d9;background:#f5f3ff">Interview</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="offered">
                <button class="kk-btn kk-btn-secondary" type="submit" style="border-color:#bae6fd;color:#0369a1;background:#f0f9ff">Send Offer</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="hired">
                <button class="kk-btn kk-btn-primary" type="submit">✓ Approve / Hire</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}" onsubmit="return confirm('Reject the full-time job and send an internship offer email?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="internship_offered">
                <button class="kk-btn kk-btn-secondary" type="submit" style="border-color:#99f6e4;color:#0f766e;background:#f0fdfa">Offer Internship</button>
            </form>
            <form method="POST" action="{{ route('admin.applications.status', $item->id) }}" onsubmit="return confirm('Reject this application?')">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="rejected">
                <button class="kk-btn kk-btn-danger" type="submit">Reject</button>
            </form>
        </div>
        <p style="margin:14px 0 0;color:#64748b;font-size:13px">
            Current status:
            <span class="kk-pill kk-pill--{{ $item->status === 'reviewed' ? 'review' : $item->status }}">{{ $item->status === 'internship_offered' ? 'internship offered' : $item->status }}</span>
        </p>
    </div>
</section>

<div class="kk-detail-grid">
    <section class="kk-card">
        <div class="kk-card__head"><h2>Candidate details</h2></div>
        <div class="kk-card__body">
            <dl class="kk-kv">
                <dt>Full name</dt><dd>{{ $item->full_name }}</dd>
                <dt>Email</dt><dd><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></dd>
                <dt>Phone</dt><dd>@if($item->phone)<a href="tel:{{ preg_replace('/\s+/', '', $item->phone) }}">{{ $item->phone }}</a>@else — @endif</dd>
                <dt>Position</dt><dd>{{ $item->position }}</dd>
                <dt>Department</dt><dd>{{ $item->department ?: '—' }}</dd>
                <dt>Job type</dt><dd>{{ $item->job_type ?: '—' }}</dd>
                <dt>Qualification</dt><dd>{{ $item->qualification ?: '—' }}</dd>
                <dt>Experience</dt><dd>{{ $item->total_experience ?: '—' }}</dd>
                <dt>Source</dt><dd>{{ $item->source ?: '—' }}</dd>
                <dt>Why join</dt><dd>{{ $item->why_join ?: '—' }}</dd>
                <dt>Submitted</dt><dd>{{ $item->created_at?->format('d M Y, h:i A') }}</dd>
            </dl>
        </div>
    </section>

    <section class="kk-card">
        <div class="kk-card__head"><h2>Documents</h2></div>
        <div class="kk-card__body" style="display:grid;gap:10px">
            @if ($item->resume_path)
                <a class="kk-btn kk-btn-secondary" href="{{ asset('storage/'.$item->resume_path) }}" target="_blank" rel="noopener">Download Resume</a>
            @else
                <p class="kk-muted" style="margin:0">No resume uploaded.</p>
            @endif
            @if ($item->cover_letter_path)
                <a class="kk-btn kk-btn-secondary" href="{{ asset('storage/'.$item->cover_letter_path) }}" target="_blank" rel="noopener">Download Cover Letter</a>
            @endif
            <a class="kk-btn kk-btn-secondary" href="{{ route('admin.module.edit', ['applications', $item->id]) }}">Edit details</a>
        </div>
    </section>
</div>
@endsection
