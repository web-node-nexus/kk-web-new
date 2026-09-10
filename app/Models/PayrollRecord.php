<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollRecord extends Model
{
    protected $fillable = [
        'employee_id',
        'month',
        'issue_date',
        'basic',
        'allowances',
        'deductions',
        'net_pay',
        'status',
        'notes',
        'receipt_path',
        'receipt_name',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'basic' => 'decimal:2',
            'allowances' => 'decimal:2',
            'deductions' => 'decimal:2',
            'net_pay' => 'decimal:2',
            'issue_date' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function recalculateNet(): void
    {
        $this->net_pay = round(
            (float) $this->basic + (float) $this->allowances - (float) $this->deductions,
            2
        );
    }

    public function monthLabel(): string
    {
        try {
            return \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->month)->format('F Y');
        } catch (\Throwable) {
            return $this->month;
        }
    }

    public function receiptNo(): string
    {
        return 'KK-PAY-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }

    public function hasUploadedReceipt(): bool
    {
        return filled($this->receipt_path);
    }

    public function receiptFileUrl(): ?string
    {
        return \App\Support\TaskMedia::url($this->receipt_path);
    }

    public function receiptIsImage(): bool
    {
        $ext = strtolower(pathinfo((string) $this->receipt_path, PATHINFO_EXTENSION));

        return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    }
}
