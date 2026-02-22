<?php

// --- Gestion des Interventions ---
use App\Http\Controllers\Intervention\InterventionController;
use App\Http\Controllers\Intervention\InterventionItemController;
use App\Http\Controllers\Intervention\InterventionTechnicianController;

Route::apiResource('interventions', InterventionController::class);

// Workflow de statut et exécution
Route::prefix('interventions/{intervention}')->group(function () {
    Route::post('start', [InterventionController::class, 'start'])->name('interventions.start');
    Route::post('complete', [InterventionController::class, 'complete'])->name('interventions.complete');
    Route::patch('status', [InterventionController::class, 'updateStatus'])->name('interventions.update-status');

    // --- Gestion du Matériel (Consommables) ---
    Route::post('items', [InterventionItemController::class, 'store'])->name('interventions.items.store');
    Route::delete('items/{item}', [InterventionItemController::class, 'destroy'])->name('interventions.items.destroy');

    // --- Gestion des Techniciens (Main-d'œuvre) ---
    Route::post('technicians', [InterventionTechnicianController::class, 'store'])->name('interventions.technicians.store');
    Route::delete('technicians/{employee_id}', [InterventionTechnicianController::class, 'detach'])->name('interventions.technicians.destroy');
});
