<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactInquiry extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'service',
        'message', 'agreed_terms', 'status',
    ];

    protected function casts(): array
    {
        return ['agreed_terms' => 'boolean'];
    }
}
