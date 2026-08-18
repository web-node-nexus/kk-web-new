@extends('layouts.app')
@section('title', 'Terms & Conditions | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Terms & Conditions',
    'subtitle' => 'Guidelines for using the TechNex website and engaging our professional services.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Terms']],
])
<section class="tn-section">
    <div class="container-tn max-w-3xl space-y-5 text-[#6b7280] leading-relaxed">
        <p>By using this website you agree to communicate honestly and lawfully. Project scope, timelines and commercial terms are confirmed in a separate agreement before engagement begins.</p>
        <p>Website content is informational. TechNex is not liable for decisions made solely on the basis of marketing materials published here.</p>
        <p>Questions? Contact <a class="font-semibold text-[#2f80ed]" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
    </div>
</section>
@endsection
