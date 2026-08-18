<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Salary Receipt {{ $record->receiptNo() }} | Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <style>
        :root { --ink:#0f172a; --muted:#64748b; --line:#e2e8f0; --navy:#0f3d5e; --teal:#0f766e; }
        * { box-sizing: border-box; }
        body { margin: 0; background: #eef2f7; color: var(--ink); font-family: Inter, Segoe UI, Arial, sans-serif; }
        .bar { display:flex; justify-content:space-between; align-items:center; gap:12px; padding:14px 18px; background:#fff; border-bottom:1px solid var(--line); position:sticky; top:0; }
        .bar a, .bar button { appearance:none; border:1px solid var(--line); background:#fff; border-radius:10px; padding:8px 12px; font-weight:650; font-size:13px; cursor:pointer; text-decoration:none; color:var(--ink); }
        .bar .print { background:#0f3d5e; color:#fff; border-color:#0f3d5e; }
        .sheet { width: min(820px, calc(100% - 32px)); margin: 24px auto 40px; background:#fff; border:1px solid var(--line); border-radius:16px; padding:32px 36px; box-shadow: 0 10px 30px rgba(15,23,42,.06); }
        .head { display:flex; justify-content:space-between; gap:16px; border-bottom:2px solid var(--navy); padding-bottom:16px; }
        .brand { display:flex; gap:12px; align-items:center; }
        .brand img { width:48px; height:48px; object-fit:contain; }
        .brand strong { display:block; font-size:18px; }
        .brand span { color:var(--muted); font-size:12px; line-height:1.45; }
        .meta { text-align:right; font-size:13px; }
        .meta b { display:block; font-size:16px; color:var(--navy); margin-bottom:4px; }
        h1 { margin:18px 0 6px; font-size:22px; letter-spacing:.04em; text-transform:uppercase; text-align:center; }
        .sub { text-align:center; color:var(--muted); margin:0 0 22px; font-size:13px; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 24px; margin-bottom:22px; font-size:14px; }
        .grid span { color:var(--muted); display:block; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; margin-bottom:2px; }
        table { width:100%; border-collapse:collapse; font-size:14px; }
        th, td { padding:10px 12px; border-bottom:1px solid var(--line); text-align:left; }
        th { background:#f8fafc; font-size:12px; color:var(--muted); text-transform:uppercase; letter-spacing:.04em; }
        td.num, th.num { text-align:right; }
        .total td { font-weight:800; font-size:16px; border-bottom:none; background:#f0fdfa; color:var(--teal); }
        .notes { margin-top:16px; font-size:13px; color:var(--muted); }
        .proof { margin-top:22px; border:1px dashed var(--line); border-radius:12px; padding:14px; }
        .proof h2 { margin:0 0 10px; font-size:13px; letter-spacing:.06em; text-transform:uppercase; color:var(--muted); }
        .proof img { max-width:100%; border-radius:8px; border:1px solid var(--line); }
        .proof a { font-weight:700; color:var(--navy); }
        .sign { display:grid; grid-template-columns:1fr 1fr; gap:24px; margin-top:48px; }
        .sign div { border-top:1px solid #94a3b8; padding-top:8px; font-size:12px; color:var(--muted); }
        .foot { margin-top:28px; font-size:11px; color:#94a3b8; text-align:center; }
        .stamp { display:inline-block; margin-top:8px; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:800; text-transform:uppercase; }
        .stamp.paid { background:#dcfce7; color:#166534; }
        .stamp.pending { background:#fef3c7; color:#92400e; }
        @media print {
            body { background:#fff; }
            .bar { display:none; }
            .sheet { width:100%; margin:0; border:none; box-shadow:none; border-radius:0; padding:0; }
        }
    </style>
</head>
<body>
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $emp = $employee;
@endphp
<div class="bar">
    <a href="{{ route('admin.payroll.index') }}">← Back to payroll</a>
    <div style="display:flex;gap:8px;align-items:center">
        @if (session('success'))
            <span style="font-size:13px;color:#0f766e;font-weight:650">{{ session('success') }}</span>
        @endif
        @if ($record->hasUploadedReceipt())
            <a href="{{ $record->receiptFileUrl() }}" target="_blank" rel="noopener">Uploaded proof</a>
        @endif
        <button class="print" type="button" onclick="window.print()">Print / Save PDF</button>
    </div>
</div>

<article class="sheet">
    <div class="head">
        <div class="brand">
            <img src="{{ asset('images/kk-logo.png') }}" alt="KK Digital">
            <div>
                <strong>{{ $company['name'] }}</strong>
                <span>{{ $company['address'] }}<br>{{ $company['phone'] }} · {{ $company['email'] }}</span>
            </div>
        </div>
        <div class="meta">
            <b>Salary Receipt</b>
            Receipt no. {{ $record->receiptNo() }}<br>
            {{ \App\Support\AppTime::now()->format('d M Y, h:i A') }}
        </div>
    </div>

    <h1>Payslip / Receipt</h1>
    <p class="sub">
        For the month of <strong>{{ $record->monthLabel() }}</strong>
        · <span class="stamp {{ $record->status === 'paid' ? 'paid' : 'pending' }}">{{ $record->status }}</span>
    </p>

    <div class="grid">
        <div><span>Employee</span><strong>{{ $emp?->name ?: '—' }}</strong></div>
        <div><span>Employee code</span><strong>{{ $emp?->employee_code ?: '—' }}</strong></div>
        <div><span>Email</span>{{ $emp?->email ?: '—' }}</div>
        <div><span>Phone</span>{{ $emp?->phone ?: '—' }}</div>
        <div><span>Role</span>{{ $emp?->role_title ?: '—' }}</div>
        <div><span>Department</span>{{ $emp?->department?->name ?: '—' }}</div>
        <div><span>Paid on</span>{{ $record->paid_at ? \App\Support\AppTime::formatDate($record->paid_at) : 'Not marked paid' }}</div>
        <div><span>Issued by</span>{{ $user->name ?? 'Admin' }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Particulars</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Basic salary</td>
                <td class="num">{{ $money($record->basic) }}</td>
            </tr>
            <tr>
                <td>Allowances</td>
                <td class="num">{{ $money($record->allowances) }}</td>
            </tr>
            <tr>
                <td>Deductions</td>
                <td class="num">− {{ $money($record->deductions) }}</td>
            </tr>
            <tr class="total">
                <td>Net pay</td>
                <td class="num">{{ $money($record->net_pay) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($record->notes)
        <p class="notes"><strong>Notes:</strong> {{ $record->notes }}</p>
    @endif

    @if ($record->hasUploadedReceipt())
        <div class="proof">
            <h2>Uploaded payment receipt</h2>
            @if ($record->receiptIsImage())
                <img src="{{ $record->receiptFileUrl() }}" alt="Payment receipt">
            @else
                <a href="{{ $record->receiptFileUrl() }}" target="_blank" rel="noopener">{{ $record->receipt_name ?: 'Open uploaded receipt' }}</a>
            @endif
        </div>
    @endif

    <div class="sign">
        <div>Employee signature</div>
        <div>Authorised signatory · {{ $company['name'] }}</div>
    </div>
    <p class="foot">Admin-only receipt · Generated from KK Digital payroll · This is a system-generated document.</p>
</article>
</body>
</html>
