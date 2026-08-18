<?php

namespace App\Mail;

use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EmployeeWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Employee $employee,
        public string $plainPassword,
        public string $loginUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Welcome to KK Digital Solution — Your Employee Panel Access',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.employee-welcome',
            with: [
                'employee' => $this->employee,
                'plainPassword' => $this->plainPassword,
                'loginUrl' => $this->loginUrl,
                'company' => 'KK Digital Solution',
                'supportEmail' => 'support.kkdigitalsolution@gmail.com',
            ],
        );
    }
}
