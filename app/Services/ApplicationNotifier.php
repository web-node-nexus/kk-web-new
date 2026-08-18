<?php

namespace App\Services;

use App\Mail\ApplicationStatusMail;
use App\Models\Interview;
use App\Models\JobApplication;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ApplicationNotifier
{
    /** Statuses that trigger candidate email */
    public static function notifiableStatuses(): array
    {
        return ['shortlisted', 'interview', 'offered', 'hired', 'internship_offered', 'rejected', 'reviewed'];
    }

    /**
     * @return array{sent: bool, skipped: ?string, error: ?string}
     */
    public static function notify(JobApplication $application, string $status): array
    {
        $interviewAt = null;
        $interviewMode = null;
        $interviewer = null;

        // Interview schedule + Approve/Hire → show on Interviews page
        if (in_array($status, ['interview', 'hired'], true)) {
            $interview = self::syncInterview($application, $status);
            if ($status === 'interview' && $interview) {
                $interviewAt = $interview->scheduled_at
                    ?->timezone(config('app.timezone', 'Asia/Kolkata'))
                    ->format('l, d F Y · h:i A');
                $interviewMode = $interview->mode ?: 'Online (Video Call)';
                $interviewer = $interview->interviewer ?: 'Talent Acquisition Team — KK Digital';
            }
        }

        if (! in_array($status, self::notifiableStatuses(), true)) {
            return ['sent' => false, 'skipped' => 'status_not_notifiable', 'error' => null];
        }

        if (! \App\Support\SiteSettings::bool('mail_application_status')) {
            return ['sent' => false, 'skipped' => 'mail_toggle_off', 'error' => 'Application status emails are turned OFF in Admin Settings.'];
        }

        if (! filter_var($application->email, FILTER_VALIDATE_EMAIL)) {
            return ['sent' => false, 'skipped' => 'invalid_email', 'error' => 'Candidate email is missing or invalid: '.($application->email ?: 'empty')];
        }

        try {
            Mail::to($application->email)->send(
                new ApplicationStatusMail(
                    $application,
                    $status,
                    $interviewAt,
                    $interviewMode,
                    $interviewer,
                )
            );

            return ['sent' => true, 'skipped' => null, 'error' => null];
        } catch (Throwable $e) {
            Log::error('Application status mail failed', [
                'application_id' => $application->id,
                'email' => $application->email,
                'status' => $status,
                'error' => $e->getMessage(),
            ]);

            return ['sent' => false, 'skipped' => 'smtp_error', 'error' => $e->getMessage()];
        }
    }

    /**
     * Create / update Interviews row from a job application (Interview or Approve).
     */
    public static function syncInterview(JobApplication $application, string $status): ?Interview
    {
        $details = collect([
            'Email: '.($application->email ?: '—'),
            'Phone: '.($application->phone ?: '—'),
            'Department: '.($application->department ?: '—'),
            'Job type: '.($application->job_type ?: '—'),
            'Qualification: '.($application->qualification ?: '—'),
            'Experience: '.($application->total_experience ?: '—'),
            'Source: '.($application->source ?: '—'),
        ])->implode("\n");

        if ($status === 'hired') {
            $payload = [
                'candidate_name' => $application->full_name,
                'position' => $application->position,
                'interviewer' => 'HR / Hiring Manager — KK Digital',
                'scheduled_at' => Carbon::now(),
                'mode' => 'Approved',
                'status' => 'approved',
                'notes' => "Approved from Job Applications.\n\n{$details}",
            ];
        } else {
            $payload = [
                'candidate_name' => $application->full_name,
                'position' => $application->position,
                'interviewer' => 'Talent Acquisition Team — KK Digital',
                'scheduled_at' => Carbon::now()->addDay()->setTime(11, 0),
                'mode' => 'Online (Video Call)',
                'status' => 'scheduled',
                'notes' => "Interview scheduled from Job Applications.\n\n{$details}",
            ];
        }

        return Interview::query()->updateOrCreate(
            ['job_application_id' => $application->id],
            $payload
        );
    }
}
