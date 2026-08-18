<?php

namespace App\Models;

use App\Support\TaskMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProjectReceipt extends Model
{
    protected $fillable = [
        'client_project_id',
        'client_project_installment_id',
        'file_path',
        'file_name',
        'amount',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(ClientProjectInstallment::class, 'client_project_installment_id');
    }

    public function fileUrl(): ?string
    {
        return TaskMedia::url($this->file_path);
    }
}
