<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StorefrontPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'title',
        'is_active',
        'blocks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'blocks' => 'array',
    ];
}
