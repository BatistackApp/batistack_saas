<?php
// --- Gestion des Interventions ---
use App\Http\Controllers\Intervention\InterventionController;
use App\Http\Controllers\Intervention\InterventionItemController;
use App\Http\Controllers\Intervention\InterventionTechnicianController;

Route::apiResource('interventions', InterventionController::class);

// Workflow de statut
Route::post('interventions/{intervention}/start', [InterventionController::class, 'start'])
    ->name('interventions.start');
Route::post('interventions/{intervention}/complete', [InterventionController::class, 'complete'])
    ->name('interventions.complete');

// --- Gestion du Matériel (Items) ---
Route::post('interventions/{intervention}/items', [InterventionItemController::class, 'store'])
    ->name('interventions.items.store');
Route::delete('interventions/{intervention}/items/{item}', [InterventionItemController::class, 'destroy'])
    ->name('interventions.items.destroy');

// --- Gestion des Techniciens (Main-d'œuvre) ---
Route::post('interventions/{intervention}/technicians', [InterventionTechnicianController::class, 'store'])
    ->name('interventions.technicians.store');
Route::delete('interventions/{intervention}/technicians/{employee_id}', [InterventionTechnicianController::class, 'detach'])
    ->name('interventions.technicians.destroy');
