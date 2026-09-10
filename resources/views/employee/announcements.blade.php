@extends('layouts.employee')
@section('title', 'Announcements')
@section('heading', 'Announcements')
@section('subheading', 'Messages from admin — for everyone or specifically for you.')

@section('content')
<div class="ep-card">
    <div class="ep-card__h"><h3>Your announcements</h3></div>
    <div class="ep-card__b" style="display:grid;gap:14px">
        @forelse ($announcements as $row)
            <article style="border:1px solid #e2e8f0;border-radius:14px;padding:16px 18px;background:#fff">
                <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;align-items:flex-start">
                    <div>
                        <h4 style="margin:0 0 4px;font-size:16px">{{ $row->title }}</h4>
                        <p class="ep-muted" style="margin:0;font-size:12px;color:#64748b">
                            {{ $row->isForEveryone() ? 'For everyone' : 'For you' }}
                            · {{ $row->published_at?->timezone(config('app.timezone','Asia/Kolkata'))->format('d M Y, h:i A') ?: $row->created_at?->format('d M Y') }}
                        </p>
                    </div>
                    <span class="ep-pill ep-pill--ok">{{ $row->isForEveryone() ? 'All' : 'Personal' }}</span>
                </div>
                @if ($row->imageUrl())
                    <div style="margin-top:12px">
                        <img src="{{ $row->imageUrl() }}" alt="" style="max-width:100%;max-height:280px;border-radius:12px;object-fit:cover;border:1px solid #e2e8f0">
                    </div>
                @endif
                <p style="margin:12px 0 0;white-space:pre-wrap;line-height:1.65;color:#334155;font-size:14px">{{ $row->body }}</p>
                @if ($row->link_url)
                    <p style="margin:12px 0 0">
                        <a href="{{ $row->link_url }}" target="_blank" rel="noopener noreferrer" style="color:#0f766e;font-weight:700;text-decoration:underline">Open link</a>
                    </p>
                @endif
            </article>
        @empty
            <div class="ep-empty">
                <strong>No announcements</strong>
                When admin publishes an announcement, it will appear here.
            </div>
        @endforelse
        <div>{{ $announcements->links() }}</div>
    </div>
</div>
@endsection
