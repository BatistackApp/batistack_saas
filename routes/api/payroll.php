<?php

use App\Http\Controllers\Payroll\PayrollPeriodController;
use App\Http\Controllers\Payroll\PayslipController;

Route::prefix('payroll')->group(function () {
    // --- Périodes ---
    Route::apiResource('periods', PayrollPeriodController::class);

    Route::get('periods', [PayrollPeriodController::class, 'index'])
        ->name('payroll.periods.index');

    Route::post('periods', [PayrollPeriodController::class, 'store'])
        ->name('payroll.periods.store');

    Route::get('periods/{period}', [PayrollPeriodController::class, 'show'])
        ->name('payroll.periods.show');

    Route::delete('periods/{period}', [PayrollPeriodController::class, 'destroy'])
        ->name('payroll.periods.destroy');

    Route::post('periods/{period}/generate', [PayrollPeriodController::class, 'generate'])
        ->name('payroll.periods.generate');

    Route::post('periods/{period}/validate', [PayrollPeriodController::class, 'validatePeriod'])
        ->name('payroll.periods.validate');

    Route::post('periods/{period}/export', [PayrollPeriodController::class, 'export'])
        ->name('payroll.periods.export');

    // --- Bulletins ---
    Route::get('periods/{period_id}/payslips', [PayslipController::class, 'index'])
        ->name('payroll.payslips.index');

    Route::get('payslips/{payslip}', [PayslipController::class, 'show'])
        ->name('payroll.payslips.show');

    Route::post('payslips/{payslip}/adjustments', [PayslipController::class, 'addAdjustment'])
        ->name('payroll.payslips.adjustments.store');

    Route::delete('payslips/{payslip}/adjustments/{payslip_line}', [PayslipController::class, 'removeAdjustment'])
        ->name('payroll.payslips.adjustments.destroy');
});
