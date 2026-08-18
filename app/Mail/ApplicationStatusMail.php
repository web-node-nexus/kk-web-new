<?php

namespace App\Mail;

use App\Models\JobApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobApplication $application,
        public string $status,
        public ?string $interviewAt = null,
        public ?string $interviewMode = null,
        public ?string $interviewer = null,
    ) {}

    public function envelope(): Envelope
    {
        $role = $this->application->position;
        $company = 'KK Digital Solution';

        $subjects = [
            'shortlisted' => "Application Shortlisted | {$role} | {$company}",
            'interview' => "Interview Invitation | {$role} | {$company}",
            'offered' => "Conditional Offer of Employment | {$role} | {$company}",
            'hired' => "Selection Confirmation | {$role} | {$company}",
            'internship_offered' => "Internship Offer | {$role} | {$company}",
            'rejected' => "Application Status Update | {$role} | {$company}",
            'reviewed' => "Application Under Review | {$role} | {$company}",
        ];

        return new Envelope(
            subject: $subjects[$this->status] ?? "Application Update | {$company}",
        );
    }

    public function content(): Content
    {
        $ref = 'KK-APP-'.str_pad((string) $this->application->id, 5, '0', STR_PAD_LEFT);

        return new Content(
            view: 'emails.application-status',
            with: [
                'application' => $this->application,
                'status' => $this->status,
                'interviewAt' => $this->interviewAt,
                'interviewMode' => $this->interviewMode ?? 'Online (Video Call)',
                'interviewer' => $this->interviewer ?? 'Talent Acquisition Team',
                'company' => 'KK Digital Solution',
                'refNo' => $ref,
                'supportEmail' => 'support.kkdigitalsolution@gmail.com',
                'companyAddress' => 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402',
                'companyPhones' => '+91 93709 21363 | +91 89319 35177',
            ],
        );
    }
}
