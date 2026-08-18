@extends('layouts.employee')
@section('title', 'Assigned Interviews')
@section('heading', 'Assigned Interviews')
@section('subheading', 'Candidates assigned to you by Admin for interview.')

@section('content')
<div class="ep-card">
    <div class="ep-card__h"><h3>Your interview queue</h3></div>
    <div class="ep-card__b ep-table-wrap">
        <table class="ep-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Position</th>
                    <th>When</th>
                    <th>Mode</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            @forelse ($interviews as $row)
                <tr>
                    <td>
                        <strong>{{ $row->candidate_name }}</strong><br>
                        <span style="color:#64748b;font-size:12px">{{ $row->application->email ?? '—' }}</span>
                    </td>
                    <td>{{ $row->position ?: '—' }}</td>
                    <td>{{ optional($row->scheduled_at)->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d M Y, h:i A') ?: '—' }}</td>
                    <td>{{ $row->mode ?: '—' }}</td>
                    <td>
                        @php $st = strtolower((string) $row->status); @endphp
                        <span class="ep-pill {{ $st === 'completed' ? 'ep-pill--ok' : ($st === 'scheduled' ? 'ep-pill--warn' : 'ep-pill--muted') }}">{{ $row->status }}</span>
                    </td>
                    <td style="text-align:right">
                        <a class="ep-btn ep-btn-primary" href="{{ route('employee.interviews.show', $row->id) }}">Open details</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <div class="ep-empty">
                            <strong>No interviews assigned yet</strong>
                            Jab admin aapka employee email use karke “Send to HR” karega, yahan dikhega.
                        </div>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $interviews->links() }}</div>
    </div>
</div>
@endsection
