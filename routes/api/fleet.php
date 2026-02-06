<?php

use App\Http\Controllers\Fleet\VehicleAssignmentController;
use App\Http\Controllers\Fleet\VehicleConsumptionController;
use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\VehicleInspectionController;

Route::prefix('fleet')->group(function () {
    Route::apiResource('vehicles', VehicleController::class);
    Route::post('vehicles/{vehicle}/sync', [VehicleController::class, 'syncApi'])
        ->name('vehicles.sync-api');

    // Affectations et Mouvements
    Route::post('assignments', [VehicleAssignmentController::class, 'store'])
        ->name('assignments.store');
    Route::patch('assignments/{assignment}/release', [VehicleAssignmentController::class, 'release'])
        ->name('assignments.release');

    // Conformité et Maintenance
    Route::post('inspections', [VehicleInspectionController::class, 'store'])
        ->name('inspections.store');

    // Consommations et Relevés
    Route::post('consumptions', [VehicleConsumptionController::class, 'store'])
        ->name('consumptions.store');
});
