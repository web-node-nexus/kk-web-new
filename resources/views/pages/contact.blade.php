@extends('layouts.app')
@section('title', 'Contact Us | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Contact Us',
    'subtitle' => 'Tell us about your initiative. Our consulting team typically responds within one business day.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Contact Us']],
])

<section class="tn-section">
    <div class="container-tn grid gap-8 lg:grid-cols-[1.2fr_0.8fr]">
            @error('form')
                <div class="tn-card mb-4 border border-red-200 bg-red-50 p-4 text-sm text-red-700">{{ $message }}</div>
            @enderror
            @if (session('success'))
                <div class="tn-card mb-4 border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">{{ session('success') }}</div>
            @endif
            <form method="POST" action="{{ route('contact.submit') }}" class="tn-card space-y-4 p-6 md:p-8">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Full name *</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Your name">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Work email *</label>
                    <input type="email" name="email" value="{{ old('email') }}" required class="form-input" placeholder="you@company.com">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="form-input" placeholder="+91 ...">
                </div>
                <div>
                    <label class="mb-1.5 block text-sm font-semibold">Company</label>
                    <input type="text" name="company" value="{{ old('company') }}" class="form-input" placeholder="Organization">
                </div>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold">How can we help?</label>
                <select name="service" class="form-input">
                    <option value="">Select a service</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->title }}" @selected(old('service') === $service->title)>{{ $service->title }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-sm font-semibold">Message *</label>
                <textarea name="message" rows="5" required class="form-input" placeholder="Share goals, timeline and constraints...">{{ old('message') }}</textarea>
                @error('message')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-start gap-2 text-sm text-[#6b7280]">
                <input type="checkbox" name="agreed_terms" value="1" class="mt-1 accent-[#2f80ed]" @checked(old('agreed_terms')) required>
                <span>I agree to the <a href="{{ route('privacy') }}" class="font-semibold text-[#2f80ed]">Privacy Policy</a>.</span>
            </label>
            <button type="submit" class="tn-btn tn-btn-primary">Send message</button>
        </form>

        <aside class="space-y-5">
            <div class="tn-media aspect-[16/10]">
                <img src="{{ asset('images/generated/hero-skyline.png') }}" alt="K&K office city">
            </div>
            <div class="tn-card p-7">
                <h2 class="m-0 text-xl font-bold leading-snug tracking-tight">Talk to K&amp;K Digital Solution</h2>
                <div class="mt-5 space-y-3 text-sm text-[#6b7280]">
                    <p><span class="block text-xs font-bold uppercase tracking-wider text-[#94a3b8]">Email</span>
                        <a class="font-semibold text-[#1a1d26] hover:text-[#2f80ed]" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
                    <p><span class="block text-xs font-bold uppercase tracking-wider text-[#94a3b8]">Phone</span>
                        @foreach ($site['phones'] ?? [] as $phone)
                            <a class="block font-semibold text-[#1a1d26] hover:text-[#2f80ed]" href="tel:{{ preg_replace('/\s+/', '', $phone) }}">{{ $phone }}</a>
                        @endforeach
                    </p>
                    <p><span class="block text-xs font-bold uppercase tracking-wider text-[#94a3b8]">Office</span>
                        {{ $site['address'] ?? '' }}
                        @if (! empty($site['address_2']))
                            <br>{{ $site['address_2'] }}
                        @endif
                    </p>
                    <p><span class="block text-xs font-bold uppercase tracking-wider text-[#94a3b8]">Hours</span>{{ $site['working_hours'] ?? '' }}</p>
                </div>
            </div>
            <div class="tn-card p-7">
                <h3 class="m-0 text-lg font-bold">Prefer a quote?</h3>
                <p class="mt-2 text-sm text-[#6b7280]">Share scope and budget for a structured proposal.</p>
                <a href="{{ route('start-project') }}" class="tn-btn tn-btn-primary mt-4 !py-2.5 text-sm">Get a Quote</a>
            </div>
        </aside>
    </div>
</section>
@endsection
