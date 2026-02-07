<?php

// --- Contrats de Location ---
use App\Http\Controllers\Locations\RentalContractController;
use App\Http\Controllers\Locations\RentalItemController;
use App\Http\Controllers\Locations\RentalStatusController;

Route::apiResource('rental-contracts', RentalContractController::class);

// Workflow de statut (Start / End)
Route::patch('rental-contracts/{rental_contract}/status', [RentalStatusController::class, 'updateStatus'])
    ->name('locations.contracts.update-status');

// --- Lignes de matériel (Items) ---
Route::post('rental-contracts/{rental_contract}/items', [RentalItemController::class, 'store'])
    ->name('locations.items.store');
Route::delete('rental-contracts/{rental_contract}/items/{rental_item}', [RentalItemController::class, 'destroy'])
    ->name('locations.items.destroy');
