<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaseStudy extends Model
{
    protected $fillable = [
        'slug', 'title', 'industry', 'folder', 'result', 'summary',
        'cover_image', 'project_url', 'video', 'sort_order', 'is_featured',
    ];

    protected function casts(): array
    {
        return ['is_featured' => 'boolean'];
    }
}
