<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', \App\Http\Controllers\Api\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::prefix('articles')->group(function () {
    Route::get('/', [\App\Http\Controllers\Articles\ArticleController::class, 'index']);
    Route::post('/', [\App\Http\Controllers\Articles\ArticleController::class, 'store']);
    Route::get('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'show']);
    Route::put('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'update']);
    Route::delete('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'destroy']);
});

Route::prefix('inventory')->group(function () {
    Route::post('/sessions', [\App\Http\Controllers\Articles\InventorySessionController::class, 'store']);
    Route::post('/sessions/{id}/count', [\App\Http\Controllers\Articles\InventorySessionController::class, 'recordCount']);
});

Route::prefix('stock')->group(function () {
    Route::post('/movements', [\App\Http\Controllers\Articles\StockMovementController::class, 'store']);
});
