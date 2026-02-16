<?php

// --- Maquettes ---
use App\Http\Controllers\Bim\BimModelController;
use App\Http\Controllers\Bim\BimObjectController;
use App\Http\Controllers\Bim\BimViewController;

Route::prefix('bim')->group(function () {
    Route::apiResource('bim-models', BimModelController::class);

    // --- Exploration 3D ---
    Route::prefix('bim-models/{bim_model}')->group(function () {

        // Objets et Métadonnées
        Route::get('objects/search', [BimObjectController::class, 'search']);
        Route::get('objects/{guid}/context', [BimObjectController::class, 'getContext']);

        // Vues Partagées
        Route::get('views', [BimViewController::class, 'index']);
        Route::post('views', [BimViewController::class, 'store']);
    });
});
