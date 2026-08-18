@extends('layouts.admin')

@section('title', 'View '.$mod['title'].' | Admin')

@section('content')
<div class="kk-pagehead">
    <div>
        <h1>{{ $mod['title'] }} detail</h1>
        <p>Record #{{ $item->id }} · received {{ $item->created_at?->diffForHumans() }}</p>
    </div>
    <div class="kk-pagehead__actions">
        <a href="{{ route('admin.module.edit', [$mod['key'], $item->id]) }}" class="kk-btn kk-btn-primary">Edit / Update status</a>
        <a href="{{ route('admin.module.index', $mod['key']) }}" class="kk-btn kk-btn-secondary">Back</a>
    </div>
</div>

<section class="kk-card">
    <div class="kk-card__body">
        <dl class="kk-kv">
            @foreach ($mod['fields'] as $field)
                @php
                    $raw = $item->{$field['name']} ?? null;
                    if ($raw instanceof \Illuminate\Support\Carbon) {
                        $display = $raw->format('d M Y, h:i A');
                    } elseif (is_bool($raw)) {
                        $display = $raw ? 'Yes' : 'No';
                    } else {
                        $display = $raw;
                    }
                @endphp
                <dt>{{ $field['label'] }}</dt>
                <dd>
                    @if (($field['type'] ?? '') === 'file')
                        @if ($raw)
                            <a class="kk-btn kk-btn-secondary kk-btn-sm" href="{{ asset('storage/'.$raw) }}" target="_blank" rel="noopener">Download file</a>
                        @else
                            —
                        @endif
                    @elseif (($field['name'] ?? '') === 'status' || (($field['type'] ?? '') === 'select' && ($field['name'] ?? '') === 'status'))
                        <span class="kk-pill kk-pill--{{ $raw === 'reviewed' ? 'review' : $raw }}">{{ $raw }}</span>
                    @else
                        {{ $display === null || $display === '' ? '—' : $display }}
                    @endif
                </dd>
            @endforeach
            <dt>Submitted</dt>
            <dd>{{ $item->created_at?->format('d M Y, h:i A') ?? '—' }}</dd>
        </dl>
    </div>
</section>
@endsection
