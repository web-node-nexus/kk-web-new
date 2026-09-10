<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientProjectRenewal extends Model
{
    public const TYPES = [
        'domain' => 'Domain',
        'server' => 'Server',
        'hosting' => 'Hosting',
        'ssl' => 'SSL Certificate',
        'consulting' => 'Consulting',
        'maintenance' => 'Maintenance',
        'other' => 'Other',
    ];

    public const STATUSES = ['upcoming', 'due', 'paid', 'cancelled'];

    protected $fillable = [
        'client_project_id',
        'type',
        'name',
        'amount',
        'renew_date',
        'status',
        'vendor',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'renew_date' => 'date',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ClientProject::class, 'client_project_id');
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? ucfirst((string) $this->type);
    }
}
