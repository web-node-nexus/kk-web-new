<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectGroupMessage extends Model
{
    protected $fillable = [
        'client_project_id',
        'employee_id',
        'message',
        'mentions',
    ];

    protected function casts(): array
    {
        return [
            'mentions' => 'array',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return array<string, mixed> */
    public function toChatArray(): array
    {
        return [
            'id' => $this->id,
            'sender' => $this->employee?->name ?? 'Member',
            'sender_id' => $this->employee_id,
            'photo' => $this->employee?->photoUrl(),
            'message' => $this->message,
            'mentions' => $this->mentions ?? [],
            'time' => optional($this->created_at)->timezone(config('app.timezone', 'Asia/Kolkata'))->format('d M, h:i A'),
        ];
    }
}
