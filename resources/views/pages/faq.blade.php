@extends('layouts.app')
@section('title', 'FAQ | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Frequently Asked Questions',
    'subtitle' => 'Straight answers about engagement models, timelines, technology and getting started with TechNex.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'FAQ']],
])

<section class="tn-section">
    <div class="container-tn max-w-3xl space-y-4">
        @foreach ($faqs as $faq)
            <details class="tn-card group p-6">
                <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-lg font-bold marker:content-none">
                    {{ $faq->question }}
                    <span class="text-[#2f80ed] transition group-open:rotate-45">+</span>
                </summary>
                <p class="mt-4 mb-0 text-sm leading-relaxed text-[#6b7280]">{{ $faq->answer }}</p>
            </details>
        @endforeach
    </div>
</section>
@include('partials.cta-band')
@endsection
