<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', \App\Http\Controllers\Api\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::prefix('stock')->group(function () {
    Route::post('/movements', [\App\Http\Controllers\Articles\StockMovementController::class, 'store']);
});
