@extends('layouts.employee')
@section('title', 'Attendance')
@section('heading', 'Attendance')
@section('subheading', 'Check in / check out — admin ko turant dikhega.')

@section('content')
<div class="ep-hero" style="margin-bottom:16px">
    <div class="ep-hero__eyebrow">Today · Mark attendance</div>
    <h2>{{ \App\Support\AppTime::now()->format('l, d F Y') }}</h2>
    <p>
        @if ($todayRow?->check_in && $todayRow?->check_out)
            You checked in at {{ \Illuminate\Support\Carbon::parse($todayRow->check_in)->format('h:i A') }}
            and out at {{ \Illuminate\Support\Carbon::parse($todayRow->check_out)->format('h:i A') }}. Admin ke paas ye record save hai.
        @elseif ($todayRow?->check_in)
            Checked in at {{ \Illuminate\Support\Carbon::parse($todayRow->check_in)->format('h:i A') }}. Don’t forget to check out when you leave.
        @else
            Start your day — Check in dabao. Admin Attendance board pe dikhega.
        @endif
    </p>
    <div class="ep-hero__actions">
        @if (! $todayRow?->check_in)
            <form method="POST" action="{{ route('employee.attendance.checkin') }}">@csrf
                <button class="ep-btn ep-btn-primary" type="submit">Check in</button>
            </form>
        @elseif (! $todayRow?->check_out)
            <form method="POST" action="{{ route('employee.attendance.checkout') }}">@csrf
                <button class="ep-btn ep-btn-navy" type="submit">Check out</button>
            </form>
        @else
            <span class="ep-btn ep-btn-ghost" style="cursor:default">Attendance complete for today</span>
        @endif
    </div>
</div>

<div class="ep-card">
    <div class="ep-card__h"><h3>Attendance history</h3></div>
    <div class="ep-card__b ep-table-wrap">
        <table class="ep-table">
            <thead><tr><th>Date</th><th>Check in</th><th>Check out</th><th>Status</th></tr></thead>
            <tbody>
            @forelse ($records as $row)
                <tr>
                    <td>{{ optional($row->date)->format('D, d M Y') }}</td>
                    <td>{{ $row->check_in ? \Illuminate\Support\Carbon::parse($row->check_in)->format('h:i A') : '—' }}</td>
                    <td>{{ $row->check_out ? \Illuminate\Support\Carbon::parse($row->check_out)->format('h:i A') : '—' }}</td>
                    <td><span class="ep-pill {{ $row->status === 'present' ? 'ep-pill--ok' : ($row->status === 'late' ? 'ep-pill--warn' : 'ep-pill--muted') }}">{{ $row->status }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4"><div class="ep-empty">No records yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $records->links() }}</div>
    </div>
</div>
@endsection
