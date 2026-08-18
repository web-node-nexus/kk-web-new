<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkTaskAssignee extends Model
{
    protected $fillable = ['work_task_id', 'employee_id', 'status', 'seen_at'];

    protected function casts(): array
    {
        return ['seen_at' => 'datetime'];
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(WorkTask::class, 'work_task_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}
