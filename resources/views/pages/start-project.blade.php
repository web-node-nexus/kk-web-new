@extends('layouts.app')
@section('title', 'Get a Quote | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Get a Quote',
    'subtitle' => 'Share your requirements. Our solution architects will respond with scope options, timeline and commercial guidance.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Get a Quote']],
])

<section class="tn-section">
    <div class="container-tn grid gap-8 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <p class="tn-eyebrow">What happens next</p>
            <h2 class="mt-3 text-2xl font-bold">A clear path from brief to proposal</h2>
            <ul class="tn-list-check">
                <li>We review your brief within one business day</li>
                <li>Discovery call to clarify goals and constraints</li>
                <li>Written proposal with phases, timeline and investment</li>
            </ul>
            <div class="tn-card mt-8 p-6">
                <p class="m-0 text-sm font-bold text-[#2f80ed]">Need help faster?</p>
                @php
                    $helpPhone = $site['phones'][0] ?? '+91 93709 21363';
                    $helpEmail = $site['email'] ?? 'support.kkdigitalsolution@gmail.com';
                @endphp
                <p class="mt-2 mb-0 text-sm text-[#6b7280]">
                    Call <a class="font-semibold text-[#2f80ed] hover:underline" href="tel:{{ preg_replace('/\s+/', '', $helpPhone) }}">{{ $helpPhone }}</a>
                    or email <a class="font-semibold text-[#2f80ed] hover:underline" href="mailto:{{ $helpEmail }}">{{ $helpEmail }}</a>
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('project.submit') }}" enctype="multipart/form-data" class="tn-card space-y-4 p-6 md:p-8">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Name *</label>
                    <input type="text" name="name" required class="form-input" value="{{ old('name') }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Work email *</label>
                    <input type="email" name="email" required class="form-input" value="{{ old('email') }}">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Phone</label>
                    <input type="text" name="phone" class="form-input" value="{{ old('phone') }}">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Company</label>
                    <input type="text" name="company" class="form-input" value="{{ old('company') }}">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Service</label>
                    <select name="service" class="form-input">
                        <option value="">Select</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->title }}">{{ $service->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Budget</label>
                    <select name="budget_range" class="form-input">
                        @foreach (['Under ₹1L','₹1L – ₹5L','₹5L – ₹15L','₹15L+','Not sure'] as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold">Timeline</label>
                <select name="timeline" class="form-input">
                    @foreach (['ASAP','1–2 months','3–6 months','Flexible'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold">Project brief *</label>
                <textarea name="description" rows="6" required class="form-input" placeholder="Goals, users, must-haves, integrations...">{{ old('description') }}</textarea>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold">Attachment (optional, max 5MB)</label>
                <input type="file" name="attachment" class="form-input">
            </div>
            <button type="submit" class="tn-btn tn-btn-primary">Submit quote request</button>
        </form>
    </div>
</section>
@endsection
