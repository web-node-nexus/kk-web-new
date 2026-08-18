@extends('layouts.admin')
@section('title', 'Overview | Admin')
@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Overview</h1>
        <p>High-level snapshot of hiring, HR and communication modules.</p>
    </div>
</div>
<div class="kk-stats" style="grid-template-columns:repeat(4,minmax(0,1fr))">
    @foreach ($cards as $card)
        <article class="kk-card kk-stat">
            <p class="kk-stat__label">{{ $card['label'] }}</p>
            <div class="kk-stat__value">{{ number_format($card['value']) }}</div>
            <div class="kk-stat__trend"><span>{{ $card['hint'] }}</span></div>
        </article>
    @endforeach
</div>
@endsection
