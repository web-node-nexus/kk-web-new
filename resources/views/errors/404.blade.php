@extends('layouts.app')

@section('title', 'Page not found | KK Digital Solution')

@section('content')
<section class="tn-section" style="min-height:60vh;display:flex;align-items:center">
    <div class="container-tn text-center" style="max-width:640px;margin:0 auto">
        <p class="tn-eyebrow">404</p>
        <h1 style="font-size:clamp(2rem,4vw,3rem);margin:8px 0 12px">This page could not be found</h1>
        <p style="color:#6b7280;line-height:1.6;margin:0 0 28px">
            The link may be broken or the page may have been moved. Use the links below to continue.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:12px;justify-content:center">
            <a href="{{ route('home') }}" class="tn-btn tn-btn-primary">Go to homepage</a>
            <a href="{{ route('case-studies') }}" class="tn-btn tn-btn-secondary">View portfolio</a>
            <a href="{{ route('contact') }}" class="tn-btn tn-btn-secondary">Contact us</a>
        </div>
    </div>
</section>
@endsection
