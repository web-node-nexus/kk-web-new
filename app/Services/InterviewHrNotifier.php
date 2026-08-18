<?php

namespace App\Services;

use App\Mail\HrInterviewAssignmentMail;
use App\Models\Employee;
use App\Models\Interview;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class InterviewHrNotifier
{
    public static function defaultHrEmail(): string
    {
        return (string) (
            \App\Support\SiteSettings::get('default_hr_email')
            ?: config('mail.hr_email')
            ?: env('HR_EMAIL', 'support.kkdigitalsolution@gmail.com')
        );
    }

    /**
     * Assign interview to HR employee panel + email HR.
     *
     * @return array{ok:bool,assigned:bool,mailed:bool,error?:string}
     */
    public static function notify(Interview $interview, ?string $hrEmail = null, ?string $meetingLink = null): array
    {
        $interview->loadMissing('application');

        if ($meetingLink) {
            $interview->meeting_link = $meetingLink;
        }

        $to = $hrEmail ?: ($interview->hr_email ?: self::defaultHrEmail());
        $to = strtolower(trim($to));

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'assigned' => false, 'mailed' => false, 'error' => 'Invalid HR email'];
        }

        $hrEmployee = Employee::query()
            ->whereRaw('LOWER(email) = ?', [$to])
            ->first();

        $interview->hr_email = $to;
        $interview->assigned_hr_employee_id = $hrEmployee?->id;
        $interview->interviewer = $interview->interviewer
            ?: ($hrEmployee?->name ? $hrEmployee->name.' (HR)' : 'HR Team');
        $interview->save();

        $assigned = (bool) $hrEmployee;
        $mailed = false;

        if (! \App\Support\SiteSettings::bool('mail_hr_interview')) {
            return [
                'ok' => true,
                'assigned' => $assigned,
                'mailed' => false,
                'error' => $assigned ? null : 'No employee found with this email — panel assignment skipped.',
            ];
        }

        try {
            Mail::to($to)->send(
                new HrInterviewAssignmentMail(
                    $interview->fresh(['application']),
                    $assigned
                        ? \App\Support\PublicUrl::to('/employee/interviews/'.$interview->id)
                        : \App\Support\PublicUrl::to('/admin/interviews/'.$interview->id),
                )
            );
            $interview->update(['hr_notified_at' => now()]);
            $mailed = true;
        } catch (Throwable $e) {
            Log::warning('HR interview mail failed', [
                'interview_id' => $interview->id,
                'hr_email' => $to,
                'error' => $e->getMessage(),
            ]);

            // Panel assignment still counts as success if HR employee was linked
            if (! $assigned) {
                return ['ok' => false, 'assigned' => false, 'mailed' => false, 'error' => $e->getMessage()];
            }
        }

        return [
            'ok' => true,
            'assigned' => $assigned,
            'mailed' => $mailed,
            'error' => $assigned ? null : 'No employee found with this email — mail sent but panel assignment skipped. Add HR in Employees with this email.',
        ];
    }
}
