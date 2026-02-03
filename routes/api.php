<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', \App\Http\Controllers\Api\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::prefix('articles')->group(function () {
    Route::prefix('article')->group(function () {
        Route::get('/', [\App\Http\Controllers\Articles\ArticleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Articles\ArticleController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'show']);
        Route::put('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'destroy']);
    });

    Route::prefix('ouvrage')->group(function () {
        Route::get('/', [\App\Http\Controllers\Articles\OuvrageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Articles\OuvrageController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'show']);
        Route::post('{id}/consume', [\App\Http\Controllers\Articles\OuvrageController::class, 'consume']);
        Route::put('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'destroy']);
    });

    Route::prefix('inventory')->group(function () {
        Route::post('/sessions', [\App\Http\Controllers\Articles\InventorySessionController::class, 'store']);
        Route::post('/sessions/{id}/count', [\App\Http\Controllers\Articles\InventorySessionController::class, 'recordCount']);
    });

    Route::prefix('stock')->group(function () {
        Route::post('/movements', [\App\Http\Controllers\Articles\StockMovementController::class, 'store']);
    });
});

Route::prefix('commerce')->group(function () {
    Route::prefix('invoices')->group(function () {
        Route::get('/', [\App\Http\Controllers\Commerce\InvoicesController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Commerce\InvoicesController::class, 'store']);
        Route::post('/create-progress', [\App\Http\Controllers\Commerce\InvoicesController::class, 'createProgress']);
        Route::get('/{id}', [\App\Http\Controllers\Commerce\InvoicesController::class, 'show']);
        Route::post('/{id}/validate', [\App\Http\Controllers\Commerce\InvoicesController::class, 'validateInvoice']);
    });

    Route::prefix('purchase')->group(function () {
        Route::get('/', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'store']);
        Route::post('/{id}/receive', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'receive']);
        Route::get('/{id}', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'show']);
    });
});
