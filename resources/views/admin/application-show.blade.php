@extends('layouts.admin')

@section('title', $application->full_name.' | Application')
@section('eyebrow', 'Application Detail')
@section('heading', $application->full_name)

@section('actions')
    <a href="{{ route('admin.applications') }}" class="tn-btn tn-btn-outline !py-2.5 !px-4 text-sm">Back to list</a>
@endsection

@section('content')
<div class="admin-detail-grid">
    <section class="admin-panel">
        <div class="admin-panel__head">
            <h2>Candidate Details</h2>
            <em class="admin-status admin-status--{{ $application->status }}">{{ $application->status }}</em>
        </div>

        <div class="admin-detail-list">
            <p><span>Position</span><strong>{{ $application->position }}</strong></p>
            <p><span>Department</span><strong>{{ $application->department ?: '—' }}</strong></p>
            <p><span>Job Type</span><strong>{{ $application->job_type ?: '—' }}</strong></p>
            <p><span>Email</span><a href="mailto:{{ $application->email }}">{{ $application->email }}</a></p>
            <p><span>Phone</span><a href="tel:{{ preg_replace('/\s+/', '', (string) $application->phone) }}">{{ $application->phone }}</a></p>
            <p><span>Gender</span><strong>{{ $application->gender ?: '—' }}</strong></p>
            <p><span>Nationality</span><strong>{{ $application->nationality ?: '—' }}</strong></p>
            <p><span>Date of Birth</span><strong>{{ $application->date_of_birth?->format('d M Y') ?: '—' }}</strong></p>
            <p><span>Address</span><strong>{{ $application->address ?: '—' }}</strong></p>
            <p><span>Submitted</span><strong>{{ $application->created_at?->format('d M Y, h:i A') }}</strong></p>
        </div>

        <h3 class="admin-subhead">Education</h3>
        <div class="admin-detail-list">
            <p><span>Qualification</span><strong>{{ $application->qualification ?: '—' }}</strong></p>
            <p><span>University</span><strong>{{ $application->university ?: '—' }}</strong></p>
            <p><span>Passing Year</span><strong>{{ $application->passing_year ?: '—' }}</strong></p>
            <p><span>Field of Study</span><strong>{{ $application->field_of_study ?: '—' }}</strong></p>
            <p><span>Percentage / CGPA</span><strong>{{ $application->percentage_cgpa ?: '—' }}</strong></p>
        </div>

        <h3 class="admin-subhead">Experience</h3>
        <div class="admin-detail-list">
            <p><span>Total Experience</span><strong>{{ $application->total_experience ?: '—' }}</strong></p>
            <p><span>Last Company</span><strong>{{ $application->last_company ?: '—' }}</strong></p>
            <p><span>Job Title</span><strong>{{ $application->job_title ?: '—' }}</strong></p>
            <p><span>Responsibilities</span><strong>{{ $application->responsibilities ?: '—' }}</strong></p>
        </div>

        @if ($application->why_join)
            <h3 class="admin-subhead">Why join us</h3>
            <p class="admin-note-text">{{ $application->why_join }}</p>
        @endif

        <p class="admin-source">Source: {{ $application->source ?: 'Not specified' }}</p>
    </section>

    <aside class="admin-side-stack">
        <section class="admin-panel">
            <div class="admin-panel__head"><h2>Update Status</h2></div>
            <form method="POST" action="{{ route('admin.applications.status', $application) }}" class="admin-status-form">
                @csrf
                @method('PATCH')
                <select name="status" class="form-input">
                    @foreach (['new', 'reviewed', 'shortlisted', 'rejected'] as $status)
                        <option value="{{ $status }}" @selected($application->status === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="tn-btn tn-btn-primary w-full !py-2.5 text-sm">Save Status</button>
            </form>
        </section>

        <section class="admin-panel">
            <div class="admin-panel__head"><h2>Attachments</h2></div>
            <div class="admin-files">
                @if ($application->resume_path)
                    <a class="tn-btn tn-btn-primary !py-2.5 text-sm" href="{{ asset('storage/'.$application->resume_path) }}" target="_blank" rel="noopener">Download Resume</a>
                @else
                    <p class="admin-empty">No resume uploaded.</p>
                @endif
                @if ($application->cover_letter_path)
                    <a class="tn-btn tn-btn-outline !py-2.5 text-sm" href="{{ asset('storage/'.$application->cover_letter_path) }}" target="_blank" rel="noopener">Download Cover Letter</a>
                @endif
            </div>
        </section>
    </aside>
</div>
@endsection
