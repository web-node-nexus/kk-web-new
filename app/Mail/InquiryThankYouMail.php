<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InquiryThankYouMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $type,
        public string $name,
        public string $email,
        public ?string $service = null,
        public ?string $summary = null,
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'contact' => 'Thank you for contacting K&K Digital Solution',
            'quote' => 'Thank you for your quote request | K&K Digital Solution',
        ];

        return new Envelope(
            subject: $subjects[$this->type] ?? 'Thank you | K&K Digital Solution',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.inquiry-thank-you',
            with: [
                'type' => $this->type,
                'name' => $this->name,
                'email' => $this->email,
                'service' => $this->service,
                'summary' => $this->summary,
                'company' => 'K&K Digital Solution',
                'supportEmail' => 'support.kkdigitalsolution@gmail.com',
                'companyAddress' => 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402',
                'companyPhones' => '+91 93709 21363 | +91 89319 35177',
            ],
        );
    }
}
