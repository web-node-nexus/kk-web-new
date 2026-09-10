<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalTodo extends Model
{
    public const PRIORITIES = ['low', 'medium', 'high'];

    protected $fillable = [
        'owner_type',
        'owner_id',
        'title',
        'notes',
        'priority',
        'due_date',
        'is_done',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'is_done' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeForAdmin($query, int $userId)
    {
        return $query->where('owner_type', 'admin')->where('owner_id', $userId);
    }

    public function scopeForEmployee($query, int $employeeId)
    {
        return $query->where('owner_type', 'employee')->where('owner_id', $employeeId);
    }

    public function markDone(): void
    {
        $this->update([
            'is_done' => true,
            'completed_at' => now(),
        ]);
    }

    public function markOpen(): void
    {
        $this->update([
            'is_done' => false,
            'completed_at' => null,
        ]);
    }
}
