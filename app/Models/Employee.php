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

    public function projectMemberships(): HasMany
    {
        return $this->hasMany(ClientProjectMember::class);
    }

    public function photoUrl(): ?string
    {
        return \App\Support\TaskMedia::url($this->photo_path);
    }

    public static function generateCode(): string
    {
        $max = 0;
        foreach (static::query()->whereNotNull('employee_code')->pluck('employee_code') as $code) {
            $code = trim((string) $code);
            if (preg_match('/^(?:KkDigital|KK-?EMP-?|KK)(\d+)$/i', $code, $m)) {
                $max = max($max, (int) $m[1]);
            } elseif (preg_match('/^(\d{1,4})$/', $code, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        // Also never collide with total employee count floor
        $max = max($max, (int) static::query()->count());

        do {
            $max++;
            $candidate = 'KkDigital'.str_pad((string) $max, 3, '0', STR_PAD_LEFT);
        } while (static::query()->where('employee_code', $candidate)->exists());

        return $candidate;
    }
}
