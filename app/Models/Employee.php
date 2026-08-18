<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    public const EMP_TYPES = ['Full-time', 'Part-time', 'Internship', 'Contract', 'Consultant'];

    public const JOB_TYPES = ['Permanent', 'Temporary', 'Internship', 'Freelance', 'Probation'];

    public const DEPARTMENTS = [
        'Engineering', 'Human Resources', 'Marketing', 'Sales', 'Accounts',
        'Operations', 'Design', 'Support', 'Product', 'Management',
    ];

    protected $fillable = [
        'department_id', 'employee_code', 'name', 'email', 'phone', 'photo_path', 'role_title',
        'employment_type', 'job_type', 'join_date', 'salary', 'status',
    ];

    protected function casts(): array
    {
        return [
            'join_date' => 'date',
            'salary' => 'decimal:2',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function leaves(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function payroll(): HasMany
    {
        return $this->hasMany(PayrollRecord::class);
    }

    public function taskAssignments(): HasMany
    {
        return $this->hasMany(WorkTaskAssignee::class);
    }

    public function photoUrl(): ?string
    {
        if (! $this->photo_path) {
            return null;
        }

        return asset('storage/'.$this->photo_path);
    }
}
