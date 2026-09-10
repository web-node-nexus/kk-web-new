<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = [
        'title',
        'body',
        'image_path',
        'link_url',
        'audience',
        'audience_type',
        'employee_id',
        'status',
        'published_at',
    ];

    public function imageUrl(): ?string
    {
        return \App\Support\TaskMedia::url($this->image_path);
    }

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isForEveryone(): bool
    {
        return ($this->audience_type ?? 'all') === 'all';
    }

    public function audienceLabel(): string
    {
        if ($this->isForEveryone()) {
            return 'All employees';
        }

        return $this->employee?->name
            ? 'Only: '.$this->employee->name
            : ($this->audience ?: 'One employee');
    }

    /** Published announcements visible to a given employee. */
    public function scopeForEmployee(Builder $query, int $employeeId): Builder
    {
        return $query
            ->where('status', 'published')
            ->where(function ($q) use ($employeeId) {
                $q->where('audience_type', 'all')
                    ->orWhere(function ($inner) use ($employeeId) {
                        $inner->where('audience_type', 'one')
                            ->where('employee_id', $employeeId);
                    });
            });
    }
}
