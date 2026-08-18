<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'job_application_id', 'candidate_name', 'position', 'interviewer',
        'scheduled_at', 'mode', 'meeting_link', 'hr_email', 'assigned_hr_employee_id',
        'hr_notified_at', 'status', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'hr_notified_at' => 'datetime',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(JobApplication::class, 'job_application_id');
    }

    public function assignedHr(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'assigned_hr_employee_id');
    }
}
