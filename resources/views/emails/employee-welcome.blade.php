<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome — {{ $company }}</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">Official Onboarding Communication</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">Congratulations!</h1>
    <p style="margin:6px 0 0;font-size:14px;color:#c5d4e8;">Welcome to the {{ $company }} family</p>
</td>
</tr>

<tr>
<td style="background:#f7f9fc;border-bottom:1px solid #e6ebf2;padding:12px 32px;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="font-size:12px;color:#5b6b7c;">Employee Code: <strong style="color:#0b1f3a;">{{ $employee->employee_code ?: 'Pending' }}</strong></td>
            <td align="right" style="font-size:12px;color:#5b6b7c;">{{ now()->timezone('Asia/Kolkata')->format('d M Y') }}</td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $employee->name }}</strong>,</p>

    <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
        We are delighted to welcome you aboard as
        <strong>{{ $employee->role_title ?: 'a valued team member' }}</strong>
        at <strong>{{ $company }}</strong>.
    </p>
    <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
        Your personal <strong>Employee Panel</strong> has been created. Please use the login credentials below to access your dashboard, profile, attendance, leave and payroll information.
    </p>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:6px;margin:0 0 20px;overflow:hidden;">
        <tr style="background:#0b1f3a;">
            <td colspan="2" style="padding:12px 16px;font-size:13px;font-weight:700;color:#ffffff;letter-spacing:.04em;">YOUR LOGIN CREDENTIALS</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;width:38%;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Login Email</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $employee->email }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Temporary Password</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:700;color:#0b1f3a;border-bottom:1px solid #eef1f5;letter-spacing:.04em;">{{ $plainPassword }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;">Panel URL</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#2f6fed;">
                <a href="{{ $loginUrl }}" style="color:#2f6fed;text-decoration:none;">{{ $loginUrl }}</a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;">
        <tr>
            <td align="center" style="padding:4px 0;">
                <a href="{{ $loginUrl }}" style="display:inline-block;background:#0b1f3a;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 28px;border-radius:6px;">
                    Open Employee Panel
                </a>
            </td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8eb;border:1px solid #f0dfb8;border-radius:6px;margin:0 0 20px;">
        <tr><td style="padding:16px 18px;">
            <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#9a6b00;font-weight:700;">Security Guidelines</p>
            <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
                • Sign in with the email and password shared above<br>
                • Change your password from <strong>My Profile</strong> after first login<br>
                • Do not share these credentials with anyone<br>
                • If you did not expect this email, contact HR immediately
            </p>
        </td></tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6ebf2;border-radius:6px;margin:0 0 24px;">
        <tr style="background:#f7f9fc;">
            <td colspan="2" style="padding:10px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5b6b7c;">Employment Snapshot</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;width:38%;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Name</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $employee->name }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Role</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $employee->role_title ?: '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Department</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $employee->department->name ?? '—' }}</td>
        </tr>
        <tr>
            <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Join date</td>
            <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ optional($employee->join_date)->format('d M Y') ?: '—' }}</td>
        </tr>
    </table>

    <p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#243447;">We look forward to working with you.</p>
    <p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#243447;">Warm regards,</p>
    <p style="margin:0;font-size:15px;font-weight:700;color:#0b1f3a;">Human Resources Team</p>
    <p style="margin:2px 0 0;font-size:14px;color:#5b6b7c;">{{ $company }}</p>
    <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#5b6b7c;">
        Queries?
        <a href="mailto:{{ $supportEmail }}" style="color:#2f6fed;text-decoration:none;">{{ $supportEmail }}</a>
    </p>
</td>
</tr>

<tr>
<td style="background:#0b1f3a;padding:20px 32px;">
    <p style="margin:0;font-size:12px;line-height:1.7;color:#9fb4d0;">
        {{ $company }}<br>
        K &amp; K Hub, Jalgaon Jamod, Maharashtra, 443402<br>
        +91 93709 21363 | +91 89319 35177
    </p>
    <p style="margin:12px 0 0;font-size:11px;line-height:1.6;color:#7a90a8;">
        This message contains confidential login information intended solely for the named employee.
        © {{ date('Y') }} {{ $company }}. All rights reserved.
    </p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
