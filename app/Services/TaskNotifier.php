<?php

namespace App\Services;

use App\Mail\TaskAssignedMail;
use App\Models\Employee;
use App\Models\EmployeeNotification;
use App\Models\WorkTask;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TaskNotifier
{
    /**
     * @param  Collection<int, Employee>|iterable<Employee>  $employees
     */
    public static function assigned(WorkTask $task, iterable $employees): int
    {
        $sent = 0;
        foreach ($employees as $employee) {
            if (! $employee instanceof Employee) {
                continue;
            }
            self::panel($employee, 'task_assigned', 'New task assigned', $task->title, '/employee/tasks/'.$task->id);
            if (self::mail($employee, $task, 'assigned')) {
                $sent++;
            }
        }

        return $sent;
    }

    public static function adminReply(WorkTask $task, ?string $preview = null): int
    {
        $task->loadMissing('assignees');
        $sent = 0;
        foreach ($task->assignees as $employee) {
            self::panel(
                $employee,
                'task_reply',
                'Admin replied on your task',
                $task->title,
                '/employee/tasks/'.$task->id
            );
            if (self::mail($employee, $task, 'reply', $preview)) {
                $sent++;
            }
        }

        return $sent;
    }

    public static function panel(Employee $employee, string $type, string $title, string $body, string $url): void
    {
        EmployeeNotification::query()->create([
            'employee_id' => $employee->id,
            'type' => $type,
            'title' => $title,
            'body' => $body,
            'url' => $url,
        ]);
    }

    protected static function mail(Employee $employee, WorkTask $task, string $kind, ?string $preview = null): bool
    {
        if (! \App\Support\SiteSettings::bool('mail_task_assigned')) {
            return false;
        }

        $email = $employee->email;
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        try {
            Mail::to($email)->send(new TaskAssignedMail($task, $employee, $kind, $preview));

            return true;
        } catch (Throwable $e) {
            Log::warning('Task mail failed', [
                'task_id' => $task->id,
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
