<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Filament\Support\Facades\FilamentAsset::register([
            \Filament\Support\Assets\Css::make('custom-stylesheet', public_path('css/custom.css')),
        ]);

        \Illuminate\Database\Eloquent\Relations\Relation::enforceMorphMap([
            'user' => \App\Models\User::class,
            'ORDER' => \App\Models\Order::class,
            'SALE' => \App\Models\Sale::class,
            'PURCHASE' => \App\Models\PurchaseOrder::class,
        ]);
    }
}
