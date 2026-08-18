@extends('layouts.admin')

@section('title', 'Inquiry #'.$item->id.' | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>Contact inquiry</h1>
        <p>Received {{ $item->created_at?->timezone(config('app.timezone','Asia/Kolkata'))->format('d M Y · h:i A') }} · #{{ $item->id }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.contacts.index') }}" class="kk-btn kk-btn-secondary">Back to list</a>
    </div>
</div>

<div style="display:grid;grid-template-columns:1.4fr 0.8fr;gap:16px;align-items:start">
    <section class="kk-card">
        <div class="kk-card__body">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:14px">
                <h2 style="margin:0;font-size:18px">{{ $item->name }}</h2>
                <span class="kk-pill kk-pill--{{ $item->status === 'new' ? 'review' : ($item->status === 'reviewed' ? 'open' : 'closed') }}">{{ $item->status }}</span>
            </div>

            <p style="margin:0 0 18px;padding:16px 18px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;white-space:pre-wrap;line-height:1.65;font-size:15px;color:#0f172a">{{ $item->message }}</p>

            <dl class="kk-kv">
                <dt>Email</dt>
                <dd><a href="mailto:{{ $item->email }}">{{ $item->email }}</a></dd>
                <dt>Phone</dt>
                <dd>
                    @if ($item->phone)
                        <a href="tel:{{ preg_replace('/\s+/', '', $item->phone) }}">{{ $item->phone }}</a>
                    @else
                        —
                    @endif
                </dd>
                <dt>Company</dt>
                <dd>{{ $item->company ?: '—' }}</dd>
                <dt>Service / topic</dt>
                <dd>{{ $item->service ?: '—' }}</dd>
            </dl>
        </div>
    </section>

    <div style="display:grid;gap:16px">
        <section class="kk-card">
            <div class="kk-card__body">
                <h3 style="margin:0 0 12px;font-size:15px">Update status</h3>
                <div style="display:grid;gap:8px">
                    <form method="POST" action="{{ route('admin.contacts.status', $item->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="reviewed">
                        <button class="kk-btn kk-btn-secondary" type="submit" style="width:100%">Mark reviewed</button>
                    </form>
                    <form method="POST" action="{{ route('admin.contacts.status', $item->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="closed">
                        <button class="kk-btn kk-btn-primary" type="submit" style="width:100%">Mark closed</button>
                    </form>
                    <form method="POST" action="{{ route('admin.contacts.status', $item->id) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="new">
                        <button class="kk-btn kk-btn-ghost" type="submit" style="width:100%">Mark as new again</button>
                    </form>
                </div>
                <p class="kk-muted" style="margin:12px 0 0;font-size:12px;line-height:1.5">Reply visitor ko unke email / phone pe karo — yahan se message save rehta hai record ke liye.</p>
            </div>
        </section>

        <section class="kk-card">
            <div class="kk-card__body">
                <h3 style="margin:0 0 12px;font-size:15px">Quick reply</h3>
                <a class="kk-btn kk-btn-secondary" style="width:100%;margin-bottom:8px" href="mailto:{{ $item->email }}?subject={{ rawurlencode('Re: Your inquiry to KK Digital') }}">Open email app</a>
                @if ($item->phone)
                    <a class="kk-btn kk-btn-ghost" style="width:100%" href="tel:{{ preg_replace('/\s+/', '', $item->phone) }}">Call {{ $item->phone }}</a>
                @endif
                <form method="POST" action="{{ route('admin.contacts.destroy', $item->id) }}" style="margin-top:14px" onsubmit="return confirm('Delete this inquiry permanently?')">
                    @csrf
                    @method('DELETE')
                    <button class="kk-btn kk-btn-danger" type="submit" style="width:100%">Delete inquiry</button>
                </form>
            </div>
        </section>
    </div>
</div>

<style>
@media (max-width: 900px) {
    .kk-pagehead + div[style*="grid-template-columns"] { grid-template-columns: 1fr !important; }
}
</style>
@endsection
