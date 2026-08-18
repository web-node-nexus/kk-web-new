<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobOpening extends Model
{
    protected $fillable = [
        'department_id',
        'title',
        'department_name',
        'employment_type',
        'location',
        'description',
        'is_open',
        'openings_count',
        'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_open' => 'boolean'];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(JobApplication::class);
    }
}
