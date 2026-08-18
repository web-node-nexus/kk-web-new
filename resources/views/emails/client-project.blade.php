<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company }}</title>
</head>
@php
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
    $received = $project->receivedAmount();
    $pending = $project->pendingAmount();
    $titles = [
        'created' => 'Project registered',
        'payment' => 'Payment received',
        'pending' => 'Payment reminder',
        'emi_added' => 'EMI schedule',
        'emi_paid' => 'EMI payment received',
        'emi_reminder' => 'EMI payment reminder',
    ];
    $title = $titles[$kind] ?? 'Project update';
@endphp
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">{{ $project->code() }}</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">{{ $company }}</h1>
    <p style="margin:6px 0 0;font-size:13px;color:#c5d4e8;">{{ $title }}</p>
</td>
</tr>

<tr>
<td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $project->client_name }}</strong>,</p>

    @if ($kind === 'created')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Thank you for choosing {{ $company }}. Your project <strong>{{ $project->name }}</strong> has been registered with us.
            Please find the project and payment summary below.
        </p>
    @elseif ($kind === 'payment')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            We have received a payment of <strong>{{ $money($extra['amount'] ?? 0) }}</strong> for <strong>{{ $project->name }}</strong>.
            Thank you. A receipt is attached if it was uploaded by our team.
        </p>
    @elseif ($kind === 'pending')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            This is a gentle reminder that a pending amount of <strong>{{ $money($pending) }}</strong> is outstanding on project
            <strong>{{ $project->name }}</strong>@if($project->due_date), due by <strong>{{ $project->due_date->format('d M Y') }}</strong>@endif.
            Kindly arrange the payment at your earliest convenience.
        </p>
    @elseif ($kind === 'emi_added')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            An EMI / installment schedule has been set for <strong>{{ $project->name }}</strong>. Please review the dates and amounts below.
        </p>
    @elseif ($kind === 'emi_paid')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            We have received EMI <strong>{{ $installment?->displayLabel() }}</strong> of
            <strong>{{ $money($extra['amount'] ?? $installment?->amount ?? 0) }}</strong> for <strong>{{ $project->name }}</strong>.
            Thank you. Receipt is attached if available.
        </p>
    @elseif ($kind === 'emi_reminder')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Reminder: <strong>{{ $installment?->displayLabel() }}</strong> of
            <strong>{{ $money($installment?->amount ?? 0) }}</strong> for <strong>{{ $project->name }}</strong>
            @if ($installment?->due_date)
                is due on <strong>{{ $installment->due_date->format('d M Y') }}</strong>
            @else
                is still pending
            @endif.
            Please complete this installment at the earliest.
        </p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6ebf2;border-radius:6px;margin:0 0 18px;">
        <tr style="background:#f7f9fc;">
            <td colspan="2" style="padding:10px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5b6b7c;">Project details</td>
        </tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c;width:40%">Project</td><td style="padding:8px 16px;font-size:14px;font-weight:700">{{ $project->name }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Project ID</td><td style="padding:8px 16px;font-size:14px">{{ $project->code() }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Client</td><td style="padding:8px 16px;font-size:14px">{{ $project->client_name }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Mobile</td><td style="padding:8px 16px;font-size:14px">{{ $project->mobile }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Email</td><td style="padding:8px 16px;font-size:14px">{{ $project->email }}</td></tr>
        @if ($project->address)
            <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Address</td><td style="padding:8px 16px;font-size:14px">{{ $project->address }}</td></tr>
        @endif
        @if ($project->due_date)
            <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Due date</td><td style="padding:8px 16px;font-size:14px">{{ $project->due_date->format('d M Y') }}</td></tr>
        @endif
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Final budget</td><td style="padding:8px 16px;font-size:14px;font-weight:700">{{ $money($project->final_budget) }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Received</td><td style="padding:8px 16px;font-size:14px;color:#0f7a45;font-weight:700">{{ $money($received) }}</td></tr>
        <tr><td style="padding:8px 16px;font-size:13px;color:#5b6b7c">Pending</td><td style="padding:8px 16px;font-size:14px;color:#b45309;font-weight:700">{{ $money($pending) }}</td></tr>
    </table>

    @if ($project->installments->isNotEmpty())
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6ebf2;border-radius:6px;margin:0 0 18px;">
            <tr style="background:#f7f9fc;">
                <td colspan="4" style="padding:10px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5b6b7c;">Installment / EMI schedule</td>
            </tr>
            <tr>
                <td style="padding:8px 16px;font-size:12px;color:#5b6b7c">#</td>
                <td style="padding:8px 16px;font-size:12px;color:#5b6b7c">Amount</td>
                <td style="padding:8px 16px;font-size:12px;color:#5b6b7c">Due</td>
                <td style="padding:8px 16px;font-size:12px;color:#5b6b7c">Status</td>
            </tr>
            @foreach ($project->installments as $row)
                <tr>
                    <td style="padding:8px 16px;font-size:13px">{{ $row->displayLabel() }}</td>
                    <td style="padding:8px 16px;font-size:13px">{{ $money($row->amount) }}</td>
                    <td style="padding:8px 16px;font-size:13px">{{ $row->due_date ? $row->due_date->format('d M Y') : '—' }}</td>
                    <td style="padding:8px 16px;font-size:13px;text-transform:capitalize">{{ $row->status }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    <p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#243447;">Warm regards,</p>
    <p style="margin:0;font-size:15px;font-weight:700;color:#0b1f3a;">Accounts Team</p>
    <p style="margin:2px 0 0;font-size:14px;color:#5b6b7c;">{{ $company }}</p>
    <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#5b6b7c;">
        For queries, write to
        <a href="mailto:{{ $supportEmail }}" style="color:#2f6fed;text-decoration:none;">{{ $supportEmail }}</a>
        @if ($companyPhone)
            or call {{ $companyPhone }}
        @endif
    </p>
</td>
</tr>

<tr>
<td style="background:#0b1f3a;padding:20px 32px;">
    <p style="margin:0;font-size:12px;line-height:1.7;color:#9fb4d0;">
        {{ $company }}<br>
        {{ $companyAddress }}<br>
        {{ $companyPhone }}
    </p>
    <p style="margin:12px 0 0;font-size:11px;line-height:1.6;color:#7a90a8;">
        This is an official project / payment email from {{ $company }}.
        © {{ date('Y') }} {{ $company }}. All rights reserved.
    </p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
