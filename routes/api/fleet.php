<?php

use App\Http\Controllers\Fleet\FineExportController;
use App\Http\Controllers\Fleet\FleetAnalyticsController;
use App\Http\Controllers\Fleet\MaintenanceController;
use App\Http\Controllers\Fleet\MaintenancePlanController;
use App\Http\Controllers\Fleet\VehicleAssignmentController;
use App\Http\Controllers\Fleet\VehicleCheckController;
use App\Http\Controllers\Fleet\VehicleChecklistTemplateController;
use App\Http\Controllers\Fleet\VehicleConsumptionController;
use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\VehicleFineController;
use App\Http\Controllers\Fleet\VehicleInspectionController;

Route::prefix('fleet')->group(function () {

    // --- 1. Gestion du Parc (Véhicules) ---
    Route::apiResource('vehicles', VehicleController::class)
        ->names('fleet.vehicles')->except(['destroy']);

    Route::prefix('vehicles/{vehicle}')->group(function () {
        Route::post('sync-api', [VehicleController::class, 'syncApi'])->name('fleet.vehicles.sync-api');
        Route::get('analytics/tco', [FleetAnalyticsController::class, 'getVehicleTco'])->name('fleet.vehicles.tco');
    });

    // --- 2. Affectations & Opérations ---
    Route::post('assignments', [VehicleAssignmentController::class, 'store'])->name('fleet.assignments.store');
    Route::patch('assignments/{assignment}/release', [VehicleAssignmentController::class, 'release'])->name('fleet.assignments.release');

    Route::post('consumptions', [VehicleConsumptionController::class, 'store'])->name('fleet.consumptions.store');
    Route::post('inspections', [VehicleInspectionController::class, 'store'])->name('fleet.inspections.store');

    // --- 3. Maintenance & Plans ---
    Route::apiResource('maintenance-plans', MaintenancePlanController::class)->names('fleet.maintenance-plans');

    Route::apiResource('maintenances', MaintenanceController::class)->names('fleet.maintenances')->except(['update', 'destroy']);
    Route::prefix('maintenances/{maintenance}')->group(function () {
        Route::patch('start', [MaintenanceController::class, 'start'])->name('fleet.maintenances.start');
        Route::patch('complete', [MaintenanceController::class, 'complete'])->name('fleet.maintenances.complete');
        Route::patch('cancel', [MaintenanceController::class, 'cancel'])->name('fleet.maintenances.cancel');
    });

    // --- 4. Contraventions (Fines) ---
    Route::prefix('fines')->group(function () {
        Route::get('export-ready', [FineExportController::class, 'index'])->name('fines.export-ready');
        Route::post('export-antai', [FineExportController::class, 'export'])->name('fines.export-antai');
    });
    Route::apiResource('fines', VehicleFineController::class)->names('fleet.fines');

    // --- 5. Sécurité & Checklist (Étape 7) ---
    // On force le paramètre à 'template' pour correspondre au Type-Hinting du Controller
    Route::apiResource('checklist-templates', VehicleChecklistTemplateController::class)
        ->parameters(['checklist-templates' => 'template'])
        ->names('fleet.checklist-templates');

    Route::prefix('checks')->group(function () {
        Route::get('/', [VehicleCheckController::class, 'index'])->name('fleet.checks.index');
        Route::post('/', [VehicleCheckController::class, 'store'])->name('fleet.checks.store');
        Route::get('/template/{vehicle}', [VehicleCheckController::class, 'getTemplateForVehicle'])->name('fleet.checks.get-template');
        Route::get('/{check}', [VehicleCheckController::class, 'show'])->name('fleet.checks.show');
    });

    // --- 6. Analytics Globaux ---
    Route::get('analytics/global', [FleetAnalyticsController::class, 'getFleetGlobalStats'])->name('fleet.analytics.global');
});
