<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeNotification extends Model
{
    protected $fillable = [
        'employee_id', 'type', 'title', 'body', 'url', 'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function isUnread(): bool
    {
        return $this->read_at === null;
    }
}
