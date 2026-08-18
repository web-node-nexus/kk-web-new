<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $company }} — Hiring Communication</title>
</head>
<body style="margin:0;padding:0;background:#eef1f5;font-family:Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#1a2332;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef1f5;padding:28px 12px;">
<tr><td align="center">
<table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 12px rgba(16,24,40,.08);">

{{-- Header --}}
<tr>
<td style="background:linear-gradient(135deg,#0b1f3a 0%,#163a63 100%);padding:28px 32px;">
    <p style="margin:0;font-size:11px;letter-spacing:.18em;text-transform:uppercase;color:#9fb4d0;">Official Hiring Communication</p>
    <h1 style="margin:8px 0 0;font-size:22px;font-weight:700;color:#ffffff;letter-spacing:.02em;">{{ $company }}</h1>
    <p style="margin:6px 0 0;font-size:13px;color:#c5d4e8;">Talent Acquisition &amp; Human Resources</p>
</td>
</tr>

{{-- Meta bar --}}
<tr>
<td style="background:#f7f9fc;border-bottom:1px solid #e6ebf2;padding:12px 32px;">
    <table width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td style="font-size:12px;color:#5b6b7c;">Reference: <strong style="color:#0b1f3a;">{{ $refNo }}</strong></td>
            <td align="right" style="font-size:12px;color:#5b6b7c;">Date: <strong style="color:#0b1f3a;">{{ now()->timezone('Asia/Kolkata')->format('d M Y') }}</strong></td>
        </tr>
    </table>
</td>
</tr>

{{-- Body --}}
<tr>
<td style="padding:32px;">

<p style="margin:0 0 16px;font-size:15px;line-height:1.6;">Dear <strong>{{ $application->full_name }}</strong>,</p>

@if ($status === 'shortlisted')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Thank you for applying for the position of <strong>{{ $application->position }}</strong> at <strong>{{ $company }}</strong>.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    After careful evaluation of your profile and credentials, we are pleased to inform you that your application has been
    <strong>shortlisted</strong> for the next stage of our recruitment process.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    Our Talent Acquisition team will contact you shortly with interview schedule and further instructions. Kindly keep your
    registered mobile number and email reachable.
</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f0f7ff;border:1px solid #d6e6f8;border-radius:6px;margin:0 0 20px;">
<tr><td style="padding:16px 18px;">
    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#2f6fed;font-weight:700;">Next Steps</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
        1. Await interview invitation email<br>
        2. Keep updated resume / portfolio ready<br>
        3. Ensure availability for the coming business days
    </p>
</td></tr>
</table>

@elseif ($status === 'interview')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    With reference to your application for <strong>{{ $application->position }}</strong>, we are pleased to invite you for an interview with <strong>{{ $company }}</strong>.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    Please find the interview details below. Kindly confirm your availability by replying to this email at the earliest.
</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #d9e2ec;border-radius:6px;margin:0 0 20px;overflow:hidden;">
<tr style="background:#0b1f3a;">
    <td colspan="2" style="padding:12px 16px;font-size:13px;font-weight:700;color:#ffffff;letter-spacing:.04em;">INTERVIEW DETAILS</td>
</tr>
<tr>
    <td style="padding:12px 16px;width:38%;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Position</td>
    <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $application->position }}</td>
</tr>
<tr>
    <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Date &amp; Time</td>
    <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $interviewAt ?? 'Will be confirmed by HR' }} (IST)</td>
</tr>
<tr>
    <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Mode</td>
    <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $interviewMode }}</td>
</tr>
<tr>
    <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;border-bottom:1px solid #eef1f5;">Interviewer</td>
    <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;border-bottom:1px solid #eef1f5;">{{ $interviewer }}</td>
</tr>
<tr>
    <td style="padding:12px 16px;font-size:13px;color:#5b6b7c;">Reference No.</td>
    <td style="padding:12px 16px;font-size:14px;font-weight:600;color:#0b1f3a;">{{ $refNo }}</td>
</tr>
</table>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#fff8eb;border:1px solid #f0dfb8;border-radius:6px;margin:0 0 20px;">
<tr><td style="padding:16px 18px;">
    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#9a6b00;font-weight:700;">Candidate Guidelines</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
        • Join 5–10 minutes before the scheduled time<br>
        • Keep a quiet environment and stable internet connection<br>
        • Have your updated CV / portfolio available for discussion<br>
        • Carry a government-issued photo ID for verification if requested<br>
        • For reschedule requests, reply to this email at least 24 hours in advance
    </p>
</td></tr>
</table>

@elseif ($status === 'offered')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Congratulations! Further to your interview for the role of <strong>{{ $application->position }}</strong>, we are pleased to extend a
    <strong>conditional offer of employment</strong> with <strong>{{ $company }}</strong>.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    A detailed offer letter covering compensation, joining date, and employment terms will be shared by our HR team shortly.
    Please treat this communication as confidential.
</p>

@elseif ($status === 'hired')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Congratulations! We are delighted to inform you that your candidature for the position of
    <strong>{{ $application->position }}</strong> has been <strong>approved and selected</strong> by <strong>{{ $company }}</strong>.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    You have successfully cleared our recruitment process. Our Human Resources team will contact you with onboarding formalities,
    documentation checklist, and joining instructions.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    We look forward to welcoming you to the {{ $company }} family and wish you a successful career journey with us.
</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eefaf3;border:1px solid #c6ead4;border-radius:6px;margin:0 0 20px;">
<tr><td style="padding:16px 18px;">
    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#0f7a45;font-weight:700;">Onboarding Preview</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
        • HR will share joining date &amp; reporting details<br>
        • Keep academic / ID / previous employment documents ready<br>
        • Complete any pending forms shared by email
    </p>
</td></tr>
</table>

@elseif ($status === 'internship_offered')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Thank you for applying for the <strong>full-time</strong> position of <strong>{{ $application->position }}</strong> at <strong>{{ $company }}</strong>.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    After careful evaluation, we will <strong>not be offering the full-time role</strong> at this stage. However, we were impressed with your profile and would like to extend an
    <strong>Internship opportunity</strong> with our team.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    This internship will help you gain hands-on experience with live projects, mentorship from our team, and a pathway to future full-time consideration based on performance.
</p>

<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#eef6ff;border:1px solid #c9ddf8;border-radius:6px;margin:0 0 20px;">
<tr><td style="padding:16px 18px;">
    <p style="margin:0 0 8px;font-size:12px;letter-spacing:.08em;text-transform:uppercase;color:#1d4ed8;font-weight:700;">Internship Offer</p>
    <p style="margin:0;font-size:14px;line-height:1.7;color:#243447;">
        • Role applied: {{ $application->position }} (Full-time not offered)<br>
        • Opportunity offered: Internship with {{ $company }}<br>
        • Next step: Reply to this email to confirm your interest<br>
        • HR will then share duration, joining date and onboarding details
    </p>
</td></tr>
</table>

<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    If you would like to accept this internship offer, please reply to this email at the earliest. We would be glad to have you learn and grow with us.
</p>

@elseif ($status === 'rejected')
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Thank you for your interest in the <strong>{{ $application->position }}</strong> opportunity at <strong>{{ $company }}</strong> and for the time you invested in our process.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    After careful consideration, we regret to inform you that we will not be progressing your application at this time.
    This decision does not reflect on your capabilities; we received a strong set of applications for this role.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    We encourage you to apply for future openings that match your skills. We wish you every success in your career.
</p>

@else
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#243447;">
    Thank you for applying for <strong>{{ $application->position }}</strong> at <strong>{{ $company }}</strong>.
    Your application is currently under review by our Talent Acquisition team.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#243447;">
    We will update you as soon as there is further progress. No action is required from your side at this stage.
</p>
@endif

{{-- Application summary --}}
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border:1px solid #e6ebf2;border-radius:6px;margin:0 0 24px;">
<tr style="background:#f7f9fc;">
    <td colspan="2" style="padding:10px 16px;font-size:12px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:#5b6b7c;">Application Summary</td>
</tr>
<tr>
    <td style="padding:10px 16px;width:38%;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Candidate Name</td>
    <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->full_name }}</td>
</tr>
<tr>
    <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Email</td>
    <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->email }}</td>
</tr>
<tr>
    <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Applied Position</td>
    <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->position }}</td>
</tr>
@if ($application->department)
<tr>
    <td style="padding:10px 16px;font-size:13px;color:#5b6b7c;border-top:1px solid #eef1f5;">Department</td>
    <td style="padding:10px 16px;font-size:14px;color:#0b1f3a;border-top:1px solid #eef1f5;">{{ $application->department }}</td>
</tr>
@endif
</table>

<p style="margin:0 0 6px;font-size:15px;line-height:1.6;color:#243447;">Warm regards,</p>
<p style="margin:0;font-size:15px;font-weight:700;color:#0b1f3a;">Talent Acquisition Team</p>
<p style="margin:2px 0 0;font-size:14px;color:#5b6b7c;">{{ $company }}</p>
<p style="margin:12px 0 0;font-size:13px;line-height:1.6;color:#5b6b7c;">
    For queries, reply to this email or write to
    <a href="mailto:{{ $supportEmail }}" style="color:#2f6fed;text-decoration:none;">{{ $supportEmail }}</a>
</p>

</td>
</tr>

{{-- Footer --}}
<tr>
<td style="background:#0b1f3a;padding:20px 32px;">
    <p style="margin:0;font-size:12px;line-height:1.7;color:#9fb4d0;">
        {{ $company }}<br>
        {{ $companyAddress }}<br>
        {{ $companyPhones }}
    </p>
    <p style="margin:12px 0 0;font-size:11px;line-height:1.6;color:#7a90a8;">
        This is a confidential communication intended solely for the named recipient. If you received this email in error, please delete it and notify the sender.
        © {{ date('Y') }} {{ $company }}. All rights reserved.
    </p>
</td>
</tr>

</table>
</td></tr>
</table>
</body>
</html>
