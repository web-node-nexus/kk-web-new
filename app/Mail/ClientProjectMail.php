<?php

namespace App\Mail;

use App\Models\ClientProject;
use App\Models\ClientProjectInstallment;
use App\Support\SiteSettings;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientProjectMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<string, mixed>  $extra
     */
    public function __construct(
        public ClientProject $project,
        public string $kind,
        public array $extra = [],
        public ?ClientProjectInstallment $installment = null,
        public ?string $receiptPath = null,
        public ?string $receiptName = null,
    ) {}

    public function envelope(): Envelope
    {
        $company = $this->companyName();
        $name = $this->project->name;

        $subjects = [
            'created' => "Project confirmation | {$name} | {$company}",
            'payment' => "Payment received | {$name} | {$company}",
            'pending' => "Payment reminder | {$name} | {$company}",
            'emi_added' => "EMI schedule | {$name} | {$company}",
            'emi_paid' => "EMI payment received | {$name} | {$company}",
            'emi_reminder' => "EMI payment reminder | {$name} | {$company}",
        ];

        return new Envelope(
            subject: $subjects[$this->kind] ?? "Project update | {$name} | {$company}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.client-project',
            with: [
                'project' => $this->project,
                'kind' => $this->kind,
                'extra' => $this->extra,
                'installment' => $this->installment,
                'company' => $this->companyName(),
                'supportEmail' => SiteSettings::get('support_email', 'support.kkdigitalsolution@gmail.com'),
                'companyAddress' => SiteSettings::get('company_address', 'K & K Hub, Jalgaon Jamod, Maharashtra, 443402'),
                'companyPhone' => SiteSettings::get('support_phone', '+91 93709 21363'),
            ],
        );
    }

    /** @return list<Attachment> */
    public function attachments(): array
    {
        if (! $this->receiptPath) {
            return [];
        }

        return [
            Attachment::fromStorageDisk('public', $this->receiptPath)
                ->as($this->receiptName ?: 'receipt'),
        ];
    }

    protected function companyName(): string
    {
        return SiteSettings::get('company_name', 'KK Digital Solution') ?: 'KK Digital Solution';
    }
}
