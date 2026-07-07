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
use App\Http\Controllers\Api\UserAddressController;
use App\Http\Controllers\Api\FavoriteController;

Route::get('/storefront/settings', [StoreSettingController::class, 'index']);
Route::get('/storefront/pages/{slug}', [StorefrontPageController::class, 'show']);
Route::get('/orders/tracking/{order_number}', [OrderController::class, 'tracking']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::put('/user/profile', [AuthController::class, 'updateProfile']);

    // Direcciones guardadas (estilo Mercado Libre)
    Route::get('/user/addresses', [UserAddressController::class, 'index']);
    Route::post('/user/addresses', [UserAddressController::class, 'store']);
    Route::put('/user/addresses/{id}', [UserAddressController::class, 'update']);
    Route::delete('/user/addresses/{id}', [UserAddressController::class, 'destroy']);
    Route::patch('/user/addresses/{id}/default', [UserAddressController::class, 'setDefault']);

    // Favoritos / Lista de Deseos
    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/favorites/toggle/{product_id}', [FavoriteController::class, 'toggle']);
    Route::post('/favorites/sync', [FavoriteController::class, 'sync']);
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

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/debug-logs', function (Request $request) {
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }
        $path = storage_path('logs/laravel.log');
        if (!file_exists($path)) return 'No log file';
        
        // Read last 100 lines
        $lines = file($path);
        $lastLines = array_slice($lines, -100);
        return response(implode("", $lastLines))->header('Content-Type', 'text/plain');
    });

    Route::get('/run-system-import-reset', function (Request $request) {
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['error' => 'Unauthorized. Admin access required.'], 403);
        }
        try { \Illuminate\Support\Facades\Artisan::call('storage:link'); } catch (\Throwable $e) {}
        \Illuminate\Support\Facades\Artisan::call('import:woocommerce', ['--clean' => true]);
        try { \Illuminate\Support\Facades\Cache::flush(); } catch (\Throwable $e) {}
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output()
        ]);
    });

    // Diagnóstico de configuración de email
    Route::get('/debug-mail-config', function (Request $request) {
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json([
            'MAIL_MAILER' => env('MAIL_MAILER', '(not set, default: log)'),
            'MAIL_HOST' => env('MAIL_HOST', '(not set)'),
            'MAIL_PORT' => env('MAIL_PORT', '(not set)'),
            'MAIL_SCHEME' => env('MAIL_SCHEME', '(not set)'),
            'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION', '(not set - DEPRECATED, use MAIL_SCHEME)'),
            'MAIL_USERNAME' => env('MAIL_USERNAME', '(not set)'),
            'MAIL_PASSWORD' => env('MAIL_PASSWORD') ? '***SET***' : '(not set)',
            'MAIL_FROM_ADDRESS' => env('MAIL_FROM_ADDRESS', '(not set)'),
            'MAIL_FROM_NAME' => env('MAIL_FROM_NAME', '(not set)'),
            'resolved_config' => [
                'default_mailer' => config('mail.default'),
                'smtp_host' => config('mail.mailers.smtp.host'),
                'smtp_port' => config('mail.mailers.smtp.port'),
                'smtp_scheme' => config('mail.mailers.smtp.scheme'),
                'smtp_username' => config('mail.mailers.smtp.username'),
                'from_address' => config('mail.from.address'),
                'from_name' => config('mail.from.name'),
            ],
        ]);
    });

    // Enviar correo de prueba
    Route::post('/debug-mail-test', function (Request $request) {
        if (!in_array($request->user()->role, ['admin', 'super_admin'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        $to = $request->input('email', $request->user()->email);
        try {
            \Illuminate\Support\Facades\Mail::raw(
                "Este es un correo de prueba desde Compra Saludable.\n\nFecha: " . now()->format('d/m/Y H:i:s') . "\nServidor: " . config('app.url'),
                function ($message) use ($to) {
                    $message->to($to)->subject('🧪 Test de Email - Compra Saludable');
                }
            );
            return response()->json([
                'status' => 'success',
                'message' => "Correo de prueba enviado exitosamente a {$to}",
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
                'trace' => explode("\n", $e->getTraceAsString()),
            ], 500);
        }
    });
});
