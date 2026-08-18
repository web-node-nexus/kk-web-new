<?php

namespace App\Services;

use App\Mail\ClientProjectMail;
use App\Models\ClientProject;
use App\Models\ClientProjectInstallment;
use App\Support\SiteSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ClientProjectMailer
{
    public static function created(ClientProject $project): bool
    {
        $project->load('installments');

        return self::send($project, 'created');
    }

    public static function payment(ClientProject $project, float $amount, ?ClientProjectInstallment $installment = null): bool
    {
        $project->load('installments');

        return self::send($project, $installment ? 'emi_paid' : 'payment', [
            'amount' => $amount,
        ], $installment, $installment?->receipt_path, $installment?->receipt_name);
    }

    public static function pendingReminder(ClientProject $project): bool
    {
        $project->load('installments');

        return self::send($project, 'pending');
    }

    public static function emiAdded(ClientProject $project): bool
    {
        $project->load('installments');

        return self::send($project, 'emi_added');
    }

    public static function emiReminder(ClientProject $project, ClientProjectInstallment $installment): bool
    {
        $project->load('installments');

        return self::send($project, 'emi_reminder', [], $installment);
    }

    /**
     * @param  array<string, mixed>  $extra
     */
    protected static function send(
        ClientProject $project,
        string $kind,
        array $extra = [],
        ?ClientProjectInstallment $installment = null,
        ?string $receiptPath = null,
        ?string $receiptName = null,
    ): bool {
        if (! SiteSettings::bool('mail_project_updates')) {
            return false;
        }

        $email = trim((string) $project->email);
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            Mail::to($email)->send(new ClientProjectMail(
                $project,
                $kind,
                $extra,
                $installment,
                $receiptPath,
                $receiptName,
            ));

            return true;
        } catch (Throwable $e) {
            Log::warning('Client project mail failed', [
                'project_id' => $project->id,
                'kind' => $kind,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
