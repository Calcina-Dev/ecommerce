<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;
use App\Http\Controllers\Api\IzipayWebhookController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\StorefrontPageController;
use App\Http\Controllers\Api\StoreSettingController;

Route::get('/storefront/settings', [StoreSettingController::class, 'index']);
Route::get('/storefront/pages/{slug}', [StorefrontPageController::class, 'show']);
Route::get('/orders/tracking/{order_number}', [OrderController::class, 'tracking']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);
});

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/google', [AuthController::class, 'googleLogin']);
    
    // Antiguos (OTP)
    Route::post('/request-otp', [AuthController::class, 'requestOtp']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
});

Route::prefix('catalog')->group(function () {
    Route::get('/home', [CatalogController::class, 'home']);
    Route::get('/products', [CatalogController::class, 'products']);
    Route::get('/filters', [CatalogController::class, 'filters']);
    Route::get('/products/{slug}', [CatalogController::class, 'productDetail']);
});

Route::post('/checkout/validate-coupon', [CheckoutController::class, 'validateCoupon']);
Route::post('/checkout', [CheckoutController::class, 'checkout']);
Route::post('/checkout/verify-izipay', [CheckoutController::class, 'verifyIzipay']);
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handleWebhook']);
Route::post('/webhooks/izipay', [IzipayWebhookController::class, 'handleWebhook']);

Route::get('/debug-logs', function () {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return 'No log file';
    
    // Read last 100 lines
    $lines = file($path);
    $lastLines = array_slice($lines, -100);
    return response(implode("", $lastLines))->header('Content-Type', 'text/plain');
});

Route::get('/run-system-import-reset', function () {
    try { \Illuminate\Support\Facades\Artisan::call('storage:link'); } catch (\Throwable $e) {}
    \Illuminate\Support\Facades\Artisan::call('import:woocommerce', ['--clean' => true]);
    try {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'SaleSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'OnlineOrderSeeder', '--force' => true]);
    } catch (\Throwable $e) {}
    try { \Illuminate\Support\Facades\Cache::flush(); } catch (\Throwable $e) {}
    return response()->json([
        'status' => 'success',
        'output' => \Illuminate\Support\Facades\Artisan::output()
    ]);
});
