@extends('layouts.admin')
@section('title', 'Messages | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Messages</h1>
        <p>Employee live chat aur website contact form ke naye messages.</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.chat.index') }}" class="kk-btn kk-btn-primary">Open live chat</a>
        <a href="{{ route('admin.contacts.index') }}" class="kk-btn kk-btn-secondary">Contact inbox</a>
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
                <strong>No new messages</strong>
                Employee chat ya website contact aate hi yahan dikhega.
            </div>
        @endforelse
    </div>
</section>
@endsection
