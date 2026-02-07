<?php

// --- Postes de Charge ---
use App\Http\Controllers\GPAO\WorkCenterController;
use App\Http\Controllers\GPAO\WorkOrderController;
use App\Http\Controllers\GPAO\WorkOrderOperationController;

Route::get('work-centers', [WorkCenterController::class, 'index'])->name('work-centers.index');
Route::post('work-centers', [WorkCenterController::class, 'store'])->name('work-centers.store');

// --- Ordres de Fabrication ---
Route::apiResource('work-orders', WorkOrderController::class);

// Clôture et Valorisation
Route::post('work-orders/{work_order}/finalize', [WorkOrderController::class, 'finalize'])
    ->name('work-orders.finalize');

// --- Opérations d'Atelier ---
Route::patch('operations/{operation}/status', [WorkOrderOperationController::class, 'updateStatus'])
    ->name('gpao.operations.update-status');
