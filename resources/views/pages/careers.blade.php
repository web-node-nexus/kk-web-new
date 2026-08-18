@extends('layouts.app')
@section('title', 'Careers | K&K')
@section('content')
@include('partials.page-hero', [
    'title' => 'Careers at K&K',
    'subtitle' => 'Join builders, designers and consultants who care about craft, ownership and customer impact.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Careers']],
])

<section class="tn-section">
    <div class="container-tn tn-split mb-12">
        <div>
            <p class="tn-eyebrow">Life at K&K</p>
            <h2 class="mt-3 text-3xl font-bold">Grow with meaningful work</h2>
            <ul class="tn-list-check">
                <li>Real products used by real customers</li>
                <li>Mentorship and clear growth paths</li>
                <li>Flexible hybrid / remote-friendly roles</li>
                <li>Competitive compensation and learning budgets</li>
            </ul>
        </div>
        <div class="tn-media aspect-[4/3]">
            <img src="{{ asset('images/generated/careers-team.png') }}" alt="K&K careers team">
        </div>
    </div>

    <div class="container-tn">
        <h2 class="mb-6 text-2xl font-bold">Open roles</h2>
        <div class="space-y-4">
            @forelse ($jobs as $job)
                <div class="tn-card flex flex-col gap-4 p-6 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-4">
                        <div class="tn-icon-tile !mb-0">
                            <img src="{{ asset('images/icons/icon-briefcase.png') }}" alt="">
                        </div>
                        <div>
                            <h3 class="m-0 text-xl font-bold">{{ $job->title }}</h3>
                            <p class="mt-1 mb-0 text-sm text-[#6b7280]">
                                {{ $job->employment_type }}
                                @if ($job->department_name) · {{ $job->department_name }} @endif
                                @if ($job->location) · {{ $job->location }} @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('careers.apply', ['job' => $job->id]) }}" class="tn-btn tn-btn-primary !py-2.5 text-sm shrink-0">Apply Now</a>
                </div>
            @empty
                <p class="text-[#6b7280]">No open roles right now — you can still <a class="font-semibold text-[#2f80ed]" href="{{ route('careers.apply') }}">submit an open application</a>.</p>
            @endforelse
        </div>
        <div class="mt-8 text-center">
            <a href="{{ route('careers.apply') }}" class="tn-btn tn-btn-outline">Apply for another role</a>
        </div>
    </div>
</section>
@include('partials.cta-band')
@endsection
