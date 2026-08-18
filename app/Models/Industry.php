<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $fillable = [
        'slug', 'title', 'description', 'color', 'sort_order', 'is_featured',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }
}
