<?php

namespace App\Mail;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeaveStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public LeaveRequest $leave,
        public string $status,
        public ?string $adminNote = null,
    ) {}

    public function envelope(): Envelope
    {
        $company = 'KK Digital Solution';
        $type = $this->leave->leave_type;

        $subjects = [
            'approved' => "Leave Approved | {$type} | {$company}",
            'rejected' => "Leave Request Update | {$type} | {$company}",
            'pending' => "Leave Request Received | {$type} | {$company}",
        ];

        return new Envelope(
            subject: $subjects[$this->status] ?? "Leave Update | {$company}",
        );
    }

    public function content(): Content
    {
        $leave = $this->leave->loadMissing('employee');
        $ref = 'KK-LV-'.str_pad((string) $leave->id, 5, '0', STR_PAD_LEFT);

        return new Content(
            view: 'emails.leave-status',
            with: [
                'leave' => $leave,
                'employee' => $leave->employee,
                'status' => $this->status,
                'adminNote' => $this->adminNote,
                'company' => 'KK Digital Solution',
                'refNo' => $ref,
                'supportEmail' => 'support.kkdigitalsolution@gmail.com',
            ],
        );
    }
}
