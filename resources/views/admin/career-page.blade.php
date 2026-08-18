@extends('layouts.admin')
@section('title', 'Career Page | Admin')
@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Career Page</h1>
        <p>Preview openings shown on the public careers website.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a class="kk-btn kk-btn-secondary" href="{{ route('careers') }}" target="_blank" rel="noopener">Open live page</a>
        <a class="kk-btn kk-btn-primary" href="{{ route('admin.module.create', 'jobs') }}">Add opening</a>
    </div>
</div>

<section class="kk-card">
    <div class="kk-card__head">
        <h2>Published openings</h2>
        <a class="kk-btn kk-btn-ghost" href="{{ route('admin.module.index', 'jobs') }}">Manage</a>
    </div>
    <div class="kk-card__body">
        <ul class="kk-joblist">
            @forelse ($openings as $job)
                <li>
                    <div>
                        <strong>{{ $job->title }}</strong>
                        <span>{{ $job->location }} · {{ $job->employment_type }}@if($job->department_name) · {{ $job->department_name }}@endif</span>
                    </div>
                    <em class="kk-pill {{ $job->is_open ? 'kk-pill--open' : 'kk-pill--closed' }}">{{ $job->is_open ? 'Open' : 'Closed' }}</em>
                </li>
            @empty
                <li><div><strong>No jobs</strong><span>Add openings to publish on careers.</span></div></li>
            @endforelse
        </ul>
    </div>
</section>
@endsection
