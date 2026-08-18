@extends('layouts.app')
@section('title', 'Privacy Policy | TechNex')
@section('content')
@include('partials.page-hero', [
    'title' => 'Privacy Policy',
    'subtitle' => 'How TechNex collects, uses and protects information shared through our website and client channels.',
    'crumbs' => [['label' => 'Home', 'href' => route('home')], ['label' => 'Privacy']],
])
<section class="tn-section">
    <div class="container-tn max-w-3xl space-y-5 text-[#6b7280] leading-relaxed">
        <p>TechNex respects your privacy. Information submitted via contact, quote or newsletter forms — including name, email, phone, company, message and optional attachments — is used only to respond to your inquiry and deliver our services.</p>
        <p>We do not sell personal data. Records are stored securely and retained as needed for business communication and legal compliance.</p>
        <p>For privacy requests, email <a class="font-semibold text-[#2f80ed]" href="mailto:{{ $site['email'] }}">{{ $site['email'] }}</a></p>
    </div>
</section>
@endsection
