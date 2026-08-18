<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveChatMessage extends Model
{
    protected $fillable = ['employee_id', 'user_id', 'sender_type', 'message', 'read_at'];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function senderName(): string
    {
        if ($this->sender_type === 'admin') {
            return $this->user?->name ?: 'Admin';
        }

        return $this->employee?->name ?: 'Employee';
    }
}
