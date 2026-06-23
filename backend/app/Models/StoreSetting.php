<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'whatsapp_number',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'contact_email',
        'store_address',
        'store_name',
        'footer_columns',
        'footer_theme',
    ];

    protected $casts = [
        'footer_columns' => 'array',
    ];
}
