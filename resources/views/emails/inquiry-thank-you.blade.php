<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company }} — Thank You</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">Official Acknowledgement</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:.02em;">{{ $company }}</h1>
    <p style="margin:6px 0 0;font-size:13px;color:#c5d4e8;">Thank you for writing to us</p>
</td>
</tr>

<tr>
<td style="padding:32px;">
    <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $name }}</strong>,</p>

    @if ($type === 'quote')
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Thank you for submitting your <strong>quote request</strong> with {{ $company }}. We have received your requirements and our consulting team will review them shortly.
        </p>
        <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
            We typically respond within <strong>one business day</strong> with next steps, a discovery discussion if needed, and a structured proposal.
        </p>
    @else
        <p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
            Thank you for contacting {{ $company }}. We have received your message and our team will get back to you soon.
        </p>
        <p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
            A consultant typically replies within <strong>one business day</strong>. If your request is urgent, you can also call us on the numbers below.
        </p>
    @endif

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eefaf3;border:1px solid #c6ead4;border-radius:6px;margin:0 0 20px;">
    <tr><td style="padding:16px 18px;">
        <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#0f7a45;font-weight:700;">Request received</p>
        <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
            Type: {{ $type === 'quote' ? 'Get a Quote' : 'Contact Us' }}<br>
            @if ($service) Service: {{ $service }}<br>@endif
            We will contact you at {{ $email }}.
        </p>
    </td></tr>
    </table>

    @if ($summary)
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6ebf2;border-radius:6px;margin:0 0 24px;">
        <tr style="background:#f7f9fc;">
            <td style="padding:10px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5b6b7c;">Your message</td>
        </tr>
        <tr>
            <td style="padding:14px 16px;font-size:14px;line-height:1.7;color:#243447;white-space:pre-line;">{{ \Illuminate\Support\Str::limit($summary, 600) }}</td>
        </tr>
        </table>
    @endif

    <p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#243447;">Warm regards,</p>
    <p style="margin:0;font-size:15px;font-weight:700;color:#0b1f3a;">Client Success Team</p>
    <p style="margin:2px 0 0;font-size:14px;color:#5b6b7c;">{{ $company }}</p>
    <p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#5b6b7c;">
        For queries, write to
        <a href="mailto:{{ $supportEmail }}" style="color:#2f6fed;text-decoration:none;">{{ $supportEmail }}</a>
    </p>
</td>
</tr>

<tr>
<td style="background:#0b1f3a;padding:20px 32px;">
    <p style="margin:0;font-size:12px;line-height:1.7;color:#9fb4d0;">
        {{ $company }}<br>
        {{ $companyAddress }}<br>
        {{ $companyPhones }}
    </p>
    <p style="margin:12px 0 0;font-size:11px;line-height:1.6;color:#7a90a8;">
        This is an automated acknowledgement. Please do not share confidential passwords or OTP on this thread.
        © {{ date('Y') }} {{ $company }}. All rights reserved.
    </p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
