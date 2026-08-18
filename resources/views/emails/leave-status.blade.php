<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company }} — Leave Update</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">HR · Leave Communication</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">{{ $company }}</h1>
    <p style="margin:6px 0 0;font-size:13px;color:#c5d4e8;">Employee Leave Status</p>
</td>
</tr>

<tr>
<td style="background:#f7f9fc;border-bottom:1px solid #e6ebf2;padding:12px 32px;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="font-size:12px;color:#5b6b7c;">Reference: <strong style="color:#0b1f3a;">{{ $refNo }}</strong></td>
            <td align="right" style="font-size:12px;color:#5b6b7c;">{{ now()->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $employee->name ?? 'Team Member' }}</strong>,</p>

    @if ($status === 'approved')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Your leave request has been <strong style="color:#0f766e;">approved</strong> by Admin / HR.
        </p>
    @elseif ($status === 'rejected')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Your leave request has been <strong style="color:#b91c1c;">rejected</strong> by Admin / HR.
        </p>
    @else
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            We have received your leave request. It is currently <strong>pending</strong> for admin approval.
        </p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:6px;margin:0 0 20px;overflow:hidden;">
        <tr style="background:#0b1f3a;">
            <td colspan="2" style="padding:12px 16px;font-size:13px;font-weight:700;color:#ffffff;letter-spacing:.04em;">LEAVE DETAILS</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;width:38%;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Type</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $leave->leave_type }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">From</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ optional($leave->start_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">To</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ optional($leave->end_date)->format('d M Y') }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Days</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $leave->days }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Status</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:700;color:#0b1f3a;border-bottom:1px solid #eef1f5;text-transform:uppercase;">{{ $status }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;">Reason</td>
            <td style="padding:12px 16px;font-size:14px;color:#0b1f3a;">{{ $leave->reason ?: '—' }}</td>
        </tr>
    </table>

    @if (!empty($adminNote))
        <p style="margin:0 0 14px;font-size:14px;line-height:1.7;color:#243447;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;">
            <strong>Note from Admin:</strong> {{ $adminNote }}
        </p>
    @endif

    <p style="margin:0;font-size:14px;line-height:1.7;color:#5b6b7c;">
        You can also check status anytime in your Employee Panel → Leaves.
    </p>
</td>
</tr>

<tr>
<td style="background:#f7f9fc;border-top:1px solid #e6ebf2;padding:18px 32px;font-size:12px;color:#5b6b7c;line-height:1.6;">
    For queries write to <a href="mailto:{{ $supportEmail }}" style="color:#163a63;">{{ $supportEmail }}</a><br>
    {{ $company }}
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
