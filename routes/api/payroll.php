<?php

use App\Http\Controllers\Payroll\PayrollPeriodController;
use App\Http\Controllers\Payroll\PayslipController;

Route::prefix('payroll')->group(function () {
    // --- Gestion des Périodes de Paie ---
    Route::apiResource('payroll-periods', PayrollPeriodController::class);

    // Génération massive des bulletins pour une période
    Route::post('payroll-periods/{payrollPeriod}/generate', [PayrollPeriodController::class, 'generatePayslips'])
        ->name('payroll-periods.generate');

    // Clôture et validation de la période
    Route::patch('payroll-periods/{payroll_period}/validate', [PayrollPeriodController::class, 'validatePeriod'])
        ->name('payroll-periods.validate');

    // --- Gestion des Bulletins Individuels ---
    Route::get('payslips/{payslip}', [PayslipController::class, 'show'])
        ->name('payslips.show');

    // Ajout d'ajustements (Primes / Avances)
    Route::post('payslips/{payslip}/adjustments', [PayslipController::class, 'addAdjustment'])
        ->name('payslips.adjustments');
});
