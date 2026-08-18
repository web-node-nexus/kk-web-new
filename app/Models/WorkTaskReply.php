<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTaskReply extends Model
{
    protected $fillable = [
        'work_task_id', 'employee_id', 'user_id', 'sender_type', 'message',
        'pdf_path', 'pdf_name', 'video_path', 'video_name', 'file_path', 'file_name',
        'link_url', 'meet_link',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
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
