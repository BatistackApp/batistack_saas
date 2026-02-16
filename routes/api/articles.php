<?php

Route::prefix('articles')->group(function () {
    Route::prefix('article')->group(function () {
        Route::get('/', [\App\Http\Controllers\Articles\ArticleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Articles\ArticleController::class, 'store']);
        Route::get('{article}', [\App\Http\Controllers\Articles\ArticleController::class, 'show']);
        Route::put('{article}', [\App\Http\Controllers\Articles\ArticleController::class, 'update']);
        Route::delete('{article}', [\App\Http\Controllers\Articles\ArticleController::class, 'destroy']);
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
        Route::post('/sessions/{inventorySession}/count', [\App\Http\Controllers\Articles\InventorySessionController::class, 'recordCount']);
        Route::post('/sessions/{inventorySession}/validate', [\App\Http\Controllers\Articles\InventorySessionController::class, 'validateSession']);
        Route::delete('/sessions/{inventorySession}', [\App\Http\Controllers\Articles\InventorySessionController::class, 'destroy']);
    });

    Route::prefix('stock')->group(function () {
        Route::post('/movements', [\App\Http\Controllers\Articles\StockMovementController::class, 'store']);
    });
});
