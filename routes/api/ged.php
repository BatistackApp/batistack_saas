<?php

use App\Http\Controllers\GED\DocumentController;

Route::prefix('ged')->group(function () {
    // Explorateur et recherche
    Route::get('/', [DocumentController::class, 'index']);

    // Documents
    Route::post('/upload', [DocumentController::class, 'store']);
    Route::get('/download/{document}', [DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Dossiers
    Route::post('/folders', [DocumentController::class, 'storeFolder']);

    // Actions de masse
    Route::post('/bulk', [DocumentController::class, 'bulk']);
});
