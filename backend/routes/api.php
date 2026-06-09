<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MercadoPagoWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
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

Route::post('/checkout', [CheckoutController::class, 'checkout']);
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handleWebhook']);
