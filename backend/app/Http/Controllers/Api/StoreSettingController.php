<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;

class StoreSettingController extends Controller
{
    public function index()
    {
        $settings = StoreSetting::first();
        
        return response()->json([
            'store_name' => $settings->store_name ?? 'Compra Saludable',
            'whatsapp_number' => $settings->whatsapp_number ?? '',
            'facebook_url' => $settings->facebook_url ?? '',
            'instagram_url' => $settings->instagram_url ?? '',
            'tiktok_url' => $settings->tiktok_url ?? '',
            'contact_email' => $settings->contact_email ?? '',
            'store_address' => $settings->store_address ?? '',
            'footer_theme' => $settings->footer_theme ?? 'light',
            'footer_columns' => $settings->footer_columns ?? [],
        ]);
    }
}
