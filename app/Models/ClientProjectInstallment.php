<?php

namespace App\Models;

use App\Support\TaskMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProjectInstallment extends Model
{
    protected $fillable = [
        'client_project_id',
        'number',
        'label',
        'amount',
        'due_date',
        'status',
        'paid_at',
        'receipt_path',
        'receipt_name',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ClientProjectReceipt::class);
    }

    public function receiptUrl(): ?string
    {
        return TaskMedia::url($this->receipt_path);
    }

    public function displayLabel(): string
    {
        return $this->label ?: ('EMI '.$this->number);
    }
}
