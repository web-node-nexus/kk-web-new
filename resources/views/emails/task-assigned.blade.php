<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company }} — Task Update</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">HR · Task Communication</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;">{{ $company }}</h1>
    <p style="margin:6px 0 0;font-size:13px;color:#c5d4e8;">
        {{ $kind === 'reply' ? 'Admin replied on your task' : 'New task assigned to you' }}
    </p>
</td>
</tr>

<tr>
<td style="background:#f7f9fc;border-bottom:1px solid #e6ebf2;padding:12px 32px;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="font-size:12px;color:#5b6b7c;">Reference: <strong style="color:#0b1f3a;">{{ $refNo }}</strong></td>
            <td align="right" style="font-size:12px;color:#5b6b7c;">{{ $assignedAt }}</td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $employee->name ?? 'Team Member' }}</strong>,</p>

    @if ($kind === 'reply')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Admin has replied on your assigned task <strong>{{ $task->title }}</strong>. Please review it in your Employee Panel.
        </p>
        @if ($replyPreview)
            <p style="margin:0 0 18px;font-size:14px;line-height:1.7;color:#243447;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;padding:12px 14px;">
                <strong>Admin message:</strong> {{ \Illuminate\Support\Str::limit($replyPreview, 400) }}
            </p>
        @endif
    @else
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            A new task has been assigned to you by Admin / HR. Please open your Employee Panel to view details, files and reply.
        </p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:6px;margin:0 0 20px;overflow:hidden;">
        <tr style="background:#0b1f3a;">
            <td colspan="2" style="padding:12px 16px;font-size:13px;font-weight:700;color:#ffffff;letter-spacing:.04em;">TASK DETAILS</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;width:38%;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Title</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $task->title }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Priority</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;text-transform:capitalize;">{{ $task->priority }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Sent at</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $assignedAt }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Due</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $dueLabel }}</td>
        </tr>
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Assigned by</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $task->creator?->name ?: 'Admin' }}</td>
        </tr>
        @if ($task->meet_link)
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Meet link</td>
            <td style="padding:12px 16px;font-size:14px;font-weight:600;border-bottom:1px solid #eef1f5;">
                <a href="{{ $task->meet_link }}" style="color:#2f6fed;text-decoration:none;">Join Meet</a>
            </td>
        </tr>
        @endif
        <tr>
            <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;">Details</td>
            <td style="padding:12px 16px;font-size:14px;color:#0b1f3a;white-space:pre-wrap;">{{ $task->description ?: 'Please open the employee panel for full details and files.' }}</td>
        </tr>
    </table>

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin:0 0 22px;">
        <tr>
            <td align="center" style="padding:4px 0;">
                <a href="{{ $panelUrl }}" style="display:inline-block;background:#0b1f3a;color:#ffffff;text-decoration:none;font-weight:700;font-size:14px;padding:12px 28px;border-radius:6px;">
                    Open in Employee Panel
                </a>
            </td>
        </tr>
    </table>

    <p style="margin:0;font-size:14px;line-height:1.7;color:#5b6b7c;">
        You can reply, attach files, and update status from <strong>My Tasks</strong> in your panel.
        @if (!empty($hasMedia)) Files / links / Meet are also available there. @endif
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
