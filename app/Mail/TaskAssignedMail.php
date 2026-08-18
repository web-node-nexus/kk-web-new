<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\WorkTask;
use App\Support\AppTime;
use App\Support\TaskMedia;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TaskAssignedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public WorkTask $task,
        public Employee $employee,
        public string $kind = 'assigned',
        public ?string $replyPreview = null,
    ) {}

    public function envelope(): Envelope
    {
        $company = 'KK Digital Solution';
        $subject = $this->kind === 'reply'
            ? "Admin replied on your task | {$this->task->title} | {$company}"
            : "New task assigned | {$this->task->title} | {$company}";

        return new Envelope(
            from: new Address((string) config('mail.from.address'), $company),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        $task = $this->task->loadMissing('creator');
        $due = $task->dueLabel();
        $panelUrl = \App\Support\PublicUrl::to('/employee/tasks/'.$task->id);

        return new Content(
            view: 'emails.task-assigned',
            with: [
                'task' => $task,
                'employee' => $this->employee,
                'kind' => $this->kind,
                'replyPreview' => $this->replyPreview,
                'dueLabel' => $due,
                'assignedAt' => AppTime::format($task->created_at),
                'company' => 'KK Digital Solution',
                'panelUrl' => $panelUrl,
                'hasMedia' => TaskMedia::hasAny($task),
                'supportEmail' => 'support.kkdigitalsolution@gmail.com',
                'refNo' => 'KK-TSK-'.str_pad((string) $task->id, 5, '0', STR_PAD_LEFT),
            ],
        );
    }
}
