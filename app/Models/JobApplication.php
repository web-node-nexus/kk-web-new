<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobApplication extends Model
{
    protected $fillable = [
        'job_opening_id',
        'position',
        'department',
        'job_type',
        'full_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'nationality',
        'address',
        'qualification',
        'university',
        'passing_year',
        'field_of_study',
        'percentage_cgpa',
        'total_experience',
        'last_company',
        'job_title',
        'responsibilities',
        'resume_path',
        'cover_letter_path',
        'why_join',
        'source',
        'declared',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'declared' => 'boolean',
        ];
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }
}
