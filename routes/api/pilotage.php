<?php

// --- Configuration des Indicateurs ---
use App\Http\Controllers\Pilotage\KpiDashboardController;
use App\Http\Controllers\Pilotage\KpiIndicatorController;
use App\Http\Controllers\Pilotage\KpiSnapshotController;

Route::prefix('pilotage/indicators')->group(function () {
    Route::get('/', [KpiIndicatorController::class, 'index'])->name('kpi.indicators.index');
    Route::post('/', [KpiIndicatorController::class, 'store'])->name('kpi.indicators.store');
    Route::get('{kpi_indicator}/history', [KpiIndicatorController::class, 'history'])->name('kpi.indicators.history');
});

// --- Tableaux de Bord (Données chaudes) ---
Route::get('pilotage/dashboards/summary', [KpiDashboardController::class, 'summary'])
    ->name('kpi.dashboards.summary');

// --- Actions Techniques ---
Route::post('pilotage/snapshots/trigger', [KpiSnapshotController::class, 'trigger'])
    ->name('kpi.snapshots.trigger')
    ->middleware('can:pilotage.manage');
