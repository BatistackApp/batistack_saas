<?php

use App\Http\Controllers\GED\DocumentController;

Route::prefix('ged')->group(function () {
    // --- Explorateur & Statistiques ---
    // Liste les dossiers et documents (supporte les filtres ?folder_id=, ?type=, ?status=)
    Route::get('/', [DocumentController::class, 'index'])->name('ged.index');

    // Récupère l'utilisation du quota (bytes utilisés, limite, pourcentage)
    Route::get('/stats', [DocumentController::class, 'stats'])->name('ged.stats');

    // --- Gestion des Documents ---
    Route::prefix('documents')->group(function () {
        // Upload d'un nouveau fichier
        Route::post('/upload', [DocumentController::class, 'store'])->name('ged.documents.store');

        // Actions sur un document spécifique
        Route::get('/{document}/download', [DocumentController::class, 'download'])->name('ged.documents.download');
        Route::patch('/{document}', [DocumentController::class, 'update'])->name('ged.documents.update');
        Route::delete('/{document}', [DocumentController::class, 'destroy'])->name('ged.documents.destroy');

        // Actions de masse (move, delete, archive, validate)
        Route::post('/bulk', [DocumentController::class, 'bulk'])->name('ged.documents.bulk');
    });

    // --- Gestion des Dossiers ---
    Route::prefix('folders')->group(function () {
        // Création d'un nouveau dossier
        Route::post('/', [DocumentController::class, 'storeFolder'])->name('ged.folders.store');

        // Note: Vous pourriez ajouter ici des routes pour update ou delete les dossiers si nécessaire
    });
});
