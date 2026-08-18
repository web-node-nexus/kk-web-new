@extends('layouts.admin')
@section('title', 'Notifications | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Notifications</h1>
        <p>New applications, leave requests, project leads and employee task replies.</p>
    </div>
</div>

<section class="kk-card">
    <div class="kk-card__body" style="display:grid;gap:10px">
        @forelse ($items as $item)
            <a href="{{ $item['url'] }}" class="kk-inbox-row {{ !empty($item['unread']) ? 'is-unread' : '' }}">
                <div>
                    <strong>{{ $item['title'] }}</strong>
                    <p>{{ $item['body'] }}</p>
                </div>
                <em>{{ $item['at'] ? \App\Support\AppTime::formatShort($item['at']) : '' }}</em>
            </a>
        @empty
            <div class="kk-empty">
                <strong>No new notifications</strong>
                Jab koi apply kare, leave maange, project bheje ya task pe reply kare — yahan dikhega.
            </div>
        @endforelse
    </div>
</section>
@endsection
