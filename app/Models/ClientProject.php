<?php

namespace App\Models;

use App\Support\TaskMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClientProject extends Model
{
    protected $fillable = [
        'logo_path',
        'name',
        'client_name',
        'mobile',
        'email',
        'address',
        'due_date',
        'final_budget',
        'status',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'final_budget' => 'decimal:2',
        ];
    }

    public function installments(): HasMany
    {
        return $this->hasMany(ClientProjectInstallment::class)->orderBy('number')->orderBy('id');
    }

    public function receipts(): HasMany
    {
        return $this->hasMany(ClientProjectReceipt::class)->latest();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function code(): string
    {
        return 'KK-PRJ-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function logoUrl(): ?string
    {
        return TaskMedia::url($this->logo_path);
    }

    public function receivedAmount(): float
    {
        if (array_key_exists('received_sum', $this->attributes) && $this->attributes['received_sum'] !== null) {
            return round((float) $this->attributes['received_sum'], 2);
        }

        return round((float) $this->installments()->where('status', 'paid')->sum('amount'), 2);
    }

    public function pendingAmount(): float
    {
        return round(max(0, (float) $this->final_budget - $this->receivedAmount()), 2);
    }

    public function nextInstallmentNumber(): int
    {
        return (int) $this->installments()->max('number') + 1;
    }

    public function syncStatus(): void
    {
        $status = $this->pendingAmount() <= 0.009 ? 'completed' : 'active';
        if ($this->status !== 'on_hold' && $this->status !== $status) {
            $this->update(['status' => $status]);
        }
    }
}
