<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Interview Assignment</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#0f766e 100%);padding:26px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.16em;text-transform:uppercase;color:#b7ebe4;">HR Interview Assignment</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">Please take this interview</h1>
    <p style="margin:6px 0 0;font-size:14px;color:#d5efe9;">Assigned from KK Digital Admin Panel</p>
</td>
</tr>

<tr>
<td style="padding:28px 32px;">
    <p style="margin:0 0 14px;font-size:15px;line-height:1.65;">Dear HR Team,</p>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
        You have been assigned to conduct an interview. Please find the candidate details and schedule below.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:8px;overflow:hidden;margin:0 0 18px;">
        <tr style="background:#0b1f3a;">
            <td colspan="2" style="padding:12px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;color:#fff;text-transform:uppercase;">Interview schedule</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;width:36%;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Date &amp; time</td>
            <td style="padding:12px 16px;font-size:15px;font-weight:700;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $whenLabel }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Mode</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $interview->mode ?: 'Online' }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Meeting / join link</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;border-bottom:1px solid #eef1f5;">
                @if ($interview->meeting_link)
                    <a href="{{ $interview->meeting_link }}" style="color:#0d9488;text-decoration:none;word-break:break-all;">{{ $interview->meeting_link }}</a>
                @else
                    <span style="color:#64748b;">Will be shared / as per HR process</span>
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;">Interviewer</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;">{{ $interview->interviewer ?: 'HR / Hiring Manager' }}</td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:8px;overflow:hidden;margin:0 0 18px;">
        <tr style="background:#f7f9fc;">
            <td colspan="2" style="padding:12px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;color:#5b6b7c;text-transform:uppercase;">Candidate details</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;width:36%;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Name</td>
            <td style="padding:10px 16px;font-size:14px;font-weight:700;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $interview->candidate_name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Position</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $interview->position ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Email</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->email ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Phone</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->phone ?? '—' }}</td>
        </tr>
    </table>

    @if ($interview->notes)
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8eb;border:1px solid #f0dfb8;border-radius:8px;margin:0 0 18px;">
        <tr><td style="padding:14px 16px;font-size:14px;line-height:1.65;color:#243447;white-space:pre-wrap;">{{ $interview->notes }}</td></tr>
    </table>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 8px;">
        <tr>
            <td align="center" style="padding:6px 0;">
                <a href="{{ $adminLink }}" style="display:inline-block;background:#0b1f3a;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 26px;border-radius:6px;">
                    Open interview in your panel
                </a>
            </td>
        </tr>
    </table>
    <p style="margin:10px 0 0;font-size:12px;color:#64748b;text-align:center;word-break:break-all;">{{ $adminLink }}</p>

    <p style="margin:22px 0 0;font-size:15px;line-height:1.6;">Please join on time and update the interview status after completion.</p>
    <p style="margin:14px 0 0;font-size:15px;font-weight:700;color:#0b1f3a;">Talent Acquisition · {{ $company }}</p>
</td>
</tr>

<tr>
<td style="background:#0b1f3a;padding:18px 32px;">
    <p style="margin:0;font-size:11px;line-height:1.6;color:#9fb4d0;">Confidential hiring communication · © {{ date('Y') }} {{ $company }}</p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
