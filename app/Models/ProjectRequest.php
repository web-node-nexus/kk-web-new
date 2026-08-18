<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectRequest extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'service',
        'budget_range', 'timeline', 'description', 'attachment_path', 'status',
    ];
}
