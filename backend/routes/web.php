<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/sales/{sale}/ticket', [\App\Http\Controllers\SaleTicketController::class, 'download'])
    ->name('sale.ticket')
    ->middleware(['web', 'auth']);
