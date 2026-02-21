<?php

// --- Contrats de Location ---
use App\Http\Controllers\Locations\RentalContractController;
use App\Http\Controllers\Locations\RentalInspectionController;
use App\Http\Controllers\Locations\RentalItemController;
use App\Http\Controllers\Locations\RentalStatusController;

Route::prefix('locations')->group(function () {

    // --- Contrats ---
    Route::apiResource('contracts', RentalContractController::class);

    // --- Workflow de statut ---
    Route::patch('contracts/{rental_contract}/status', [RentalStatusController::class, 'updateStatus'])
        ->name('locations.contracts.update-status');

    // --- Éléments du contrat (Items) ---
    Route::prefix('contracts/{rental_contract}')->group(function () {
        Route::post('items', [RentalItemController::class, 'store'])->name('locations.items.store');
        Route::delete('items/{rental_item}', [RentalItemController::class, 'destroy'])->name('locations.items.destroy');

        // --- États des lieux ---
        Route::post('inspections', [RentalInspectionController::class, 'store'])->name('locations.inspections.store');
    });

});
