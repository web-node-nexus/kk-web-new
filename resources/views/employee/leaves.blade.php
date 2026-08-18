@extends('layouts.employee')
@section('title', 'Leaves')
@section('heading', 'Leaves')
@section('subheading', 'Apply with reason. Leave tab approve hoti hai jab admin Approve kare — phir email aayegi.')

@section('content')
<div class="ep-grid-2">
    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>Apply for leave</h3></div>
        <div class="ep-card__b">
            <form class="ep-form ep-form--2" method="POST" action="{{ route('employee.leaves.store') }}">
                @csrf
                <div class="ep-field">
                    <label for="leave_type">Leave type</label>
                    <select id="leave_type" name="leave_type" required>
                        @foreach (['Casual Leave', 'Sick Leave', 'Privilege Leave', 'Work From Home', 'Unpaid Leave'] as $type)
                            <option value="{{ $type }}" @selected(old('leave_type') === $type)>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="ep-field">
                    <label for="days_hint">Status after submit</label>
                    <input id="days_hint" type="text" value="Pending — waiting for admin" disabled>
                </div>
                <div class="ep-field">
                    <label for="start_date">From</label>
                    <input id="start_date" type="date" name="start_date" value="{{ old('start_date') }}" required>
                </div>
                <div class="ep-field">
                    <label for="end_date">To</label>
                    <input id="end_date" type="date" name="end_date" value="{{ old('end_date') }}" required>
                </div>
                <div class="ep-field ep-field--full">
                    <label for="reason">Reason *</label>
                    <textarea id="reason" name="reason" rows="3" required minlength="5" placeholder="Why do you need this leave? (required)">{{ old('reason') }}</textarea>
                </div>
                <div class="ep-field ep-field--full">
                    <button class="ep-btn ep-btn-primary" type="submit">Submit leave request</button>
                </div>
            </form>
        </div>
    </div>

    <div class="ep-card" style="margin:0">
        <div class="ep-card__h"><h3>How it works</h3></div>
        <div class="ep-card__b" style="color:#475569;font-size:14px;line-height:1.7">
            <p style="margin-top:0">1. Leave form bharo — <strong>reason zaroori</strong> hai.</p>
            <p>2. Request admin ke <strong>Leave Requests</strong> me Pending dikhegi.</p>
            <p>3. Admin <strong>Approve</strong> kare → leave approved + aapko <strong>email</strong> jayegi.</p>
            <p style="margin-bottom:0">Reject hone par bhi email me message aayega.</p>
        </div>
    </div>
</div>

<div class="ep-card">
    <div class="ep-card__h"><h3>My leave history</h3></div>
    <div class="ep-card__b ep-table-wrap">
        <table class="ep-table">
            <thead><tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Reason</th></tr></thead>
            <tbody>
            @forelse ($leaves as $row)
                @php $st = strtolower((string) $row->status); @endphp
                <tr>
                    <td>{{ $row->leave_type }}</td>
                    <td>{{ optional($row->start_date)->format('d M Y') }}</td>
                    <td>{{ optional($row->end_date)->format('d M Y') }}</td>
                    <td>{{ $row->days }}</td>
                    <td><span class="ep-pill {{ $st === 'approved' ? 'ep-pill--ok' : ($st === 'pending' ? 'ep-pill--warn' : ($st === 'rejected' ? 'ep-pill--bad' : 'ep-pill--muted')) }}">{{ $row->status }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit($row->reason, 40) ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="6"><div class="ep-empty">No leave history yet.</div></td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:14px">{{ $leaves->links() }}</div>
    </div>
</div>
@endsection
