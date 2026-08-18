<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkTask extends Model
{
    protected $fillable = [
        'title', 'description', 'priority', 'due_date', 'due_at', 'status', 'assign_all', 'created_by',
        'pdf_path', 'pdf_name', 'video_path', 'video_name', 'file_path', 'file_name',
        'link_url', 'meet_link',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'due_at' => 'datetime',
            'assign_all' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'work_task_assignees')
            ->withPivot(['status', 'seen_at'])
            ->withTimestamps();
    }

    public function assignmentRows(): HasMany
    {
        return $this->hasMany(WorkTaskAssignee::class);
    }

    public function replies(): HasMany
    {
        return $this->hasMany(WorkTaskReply::class)->oldest();
    }

    public function dueLabel(): string
    {
        if ($this->due_at) {
            return \App\Support\AppTime::format($this->due_at);
        }

        if ($this->due_date) {
            return \App\Support\AppTime::formatDate($this->due_date);
        }

        return '—';
    }

    public function audienceLabel(): string
    {
        if ($this->assign_all) {
            return 'All employees';
        }

        $count = $this->relationLoaded('assignees')
            ? $this->assignees->count()
            : $this->assignees()->count();

        if ($count === 1) {
            $name = $this->relationLoaded('assignees')
                ? $this->assignees->first()?->name
                : $this->assignees()->first()?->name;

            return $name ?: '1 employee';
        }

        return $count.' employees';
    }

    public function refreshStatusFromAssignees(): void
    {
        $rows = $this->assignmentRows()->get();
        if ($rows->isEmpty()) {
            return;
        }

        $total = $rows->count();
        $done = $rows->where('status', 'completed')->count();
        $active = $rows->whereIn('status', ['in_progress', 'completed'])->count();

        $status = 'open';
        if ($done === $total) {
            $status = 'done';
        } elseif ($active > 0) {
            $status = 'in_progress';
        }

        if ($this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
