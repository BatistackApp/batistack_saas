<?php

use App\Http\Controllers\Fleet\FleetAnalyticsController;
use App\Http\Controllers\Fleet\MaintenanceController;
use App\Http\Controllers\Fleet\MaintenancePlanController;
use App\Http\Controllers\Fleet\VehicleAssignmentController;
use App\Http\Controllers\Fleet\VehicleConsumptionController;
use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\VehicleFineController;
use App\Http\Controllers\Fleet\VehicleInspectionController;

Route::prefix('fleet')->group(function () {
    // --- Gestion des Véhicules (CRUD) ---
    // Correspond aux tests : /api/fleet/vehicles
    Route::apiResource('vehicles', VehicleController::class);

    // --- Actions spécifiques aux véhicules ---
    Route::prefix('vehicles/{vehicle}')->group(function () {
        Route::post('sync-api', [VehicleController::class, 'syncApi'])
            ->name('vehicles.sync-api');

        // Suivi TCO par véhicule (Issue #35)
        Route::get('analytics/tco', [FleetAnalyticsController::class, 'getVehicleTco'])
            ->name('vehicles.analytics.tco');
    });

    // --- Suivi Global ---
    Route::get('analytics/global', [FleetAnalyticsController::class, 'getFleetGlobalStats'])
        ->name('fleet.analytics.global');

    // --- Opérations Flotte (Affectations, Consommations, Inspections) ---
    Route::prefix('vehicles')->group(function () {

        // Affectations (Correspond au test : /api/fleet/vehicles/assignments)
        Route::post('assignments', [VehicleAssignmentController::class, 'store'])
            ->name('vehicle-assignments.store');

        Route::patch('assignments/{assignment}/release', [VehicleAssignmentController::class, 'release'])
            ->name('vehicle-assignments.release');

        // Consommations & Pleins
        Route::post('consumptions', [VehicleConsumptionController::class, 'store'])
            ->name('vehicle-consumptions.store');

        // Inspections & Maintenance
        Route::post('inspections', [VehicleInspectionController::class, 'store'])
            ->name('vehicle-inspections.store');
    });

    // --- Maintenance (Nouveautés Étape 7) ---
    Route::apiResource('maintenance-plans', MaintenancePlanController::class);

    Route::apiResource('maintenances', MaintenanceController::class);
    Route::prefix('maintenances/{maintenance}')->group(function () {
        Route::patch('start', [MaintenanceController::class, 'start'])->name('maintenances.start');
        Route::patch('complete', [MaintenanceController::class, 'complete'])->name('maintenances.complete');
        Route::patch('cancel', [MaintenanceController::class, 'cancel'])->name('maintenances.cancel');
    });

    Route::prefix('fines')->group(function () {
        Route::get('export-ready', [\App\Http\Controllers\Fleet\FineExportController::class, 'index'])
            ->name('fines.export-ready');

        Route::post('export-antai', [\App\Http\Controllers\Fleet\FineExportController::class, 'export'])
            ->name('fines.export-antai');

        Route::apiResource('fines', VehicleFineController::class)
            ->names([
                'index'   => 'fleet.fines.index',
                'store'   => 'fleet.fines.store',
                'show'    => 'fleet.fines.show',
                'update'  => 'fleet.fines.update',
                'destroy' => 'fleet.fines.destroy',
            ]);
    });
});
