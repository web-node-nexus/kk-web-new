@extends('layouts.employee')
@section('title', 'Notifications')
@section('heading', 'Notifications')
@section('subheading', 'Task assignments and admin updates appear here with day and time.')

@section('content')
<div class="ep-card">
    <div class="ep-card__h">
        <h3>Your notifications</h3>
        <form method="POST" action="{{ route('employee.notifications.read-all') }}">
            @csrf
            <button class="ep-btn ep-btn-ghost" type="submit">Mark all read</button>
        </form>
    </div>
    <div class="ep-card__b" style="display:grid;gap:10px">
        @forelse ($notifications as $note)
            <a href="{{ route('employee.notifications.open', $note->id) }}" class="ep-note-row {{ $note->isUnread() ? 'is-unread' : '' }}">
                <div>
                    <strong>{{ $note->title }}</strong>
                    <p>{{ $note->body }}</p>
                </div>
                <em>{{ \App\Support\AppTime::format($note->created_at) }}</em>
            </a>
        @empty
            <div class="ep-empty">
                <strong>No notifications</strong>
                Jab admin aapko task dega, yahan aur email dono pe update aayega.
            </div>
        @endforelse
        <div>{{ $notifications->links() }}</div>
    </div>
</div>
@endsection
