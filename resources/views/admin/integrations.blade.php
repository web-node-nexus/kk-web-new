@extends('layouts.admin')
@section('title', 'Integrations | Admin')
@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Integrations</h1>
        <p>Connect tools used across hiring and communication.</p>
    </div>
</div>
<div class="kk-grid-2eq">
    @foreach ($items as $item)
        <article class="kk-card">
            <div class="kk-card__body" style="display:flex;justify-content:space-between;gap:16px;align-items:center">
                <div>
                    <h3 style="margin:0 0 4px;font-size:15px">{{ $item['name'] }}</h3>
                    <p style="margin:0;color:#64748b;font-size:13px">{{ $item['desc'] }}</p>
                </div>
                <span class="kk-pill kk-pill--{{ $item['status'] === 'connected' ? 'active' : ($item['status'] === 'pending' ? 'review' : 'closed') }}">{{ $item['status'] }}</span>
            </div>
        </article>
    @endforeach
</div>
@endsection
