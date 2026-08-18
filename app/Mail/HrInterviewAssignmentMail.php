<?php

namespace App\Mail;

use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class HrInterviewAssignmentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Interview $interview,
        public string $adminLink,
    ) {}

    public function envelope(): Envelope
    {
        $when = $this->interview->scheduled_at
            ? $this->interview->scheduled_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d M Y, h:i A')
            : 'TBD';

        return new Envelope(
            subject: 'Interview Assignment — '.$this->interview->candidate_name.' · '.$when,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.hr-interview-assignment',
            with: [
                'interview' => $this->interview,
                'application' => $this->interview->application,
                'adminLink' => $this->adminLink,
                'company' => 'KK Digital Solution',
                'whenLabel' => $this->interview->scheduled_at
                    ? $this->interview->scheduled_at->timezone(config('app.timezone', 'Asia/Kolkata'))->format('l, d F Y · h:i A').' (IST)'
                    : 'To be confirmed',
            ],
        );
    }
}
