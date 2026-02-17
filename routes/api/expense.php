<?php

use App\Http\Controllers\Expense\ExpenseApprovalController;
use App\Http\Controllers\Expense\ExpenseItemController;
use App\Http\Controllers\Expense\ExpenseReportController;

Route::prefix('expense')->group(function () {
    // Gestion des rapports (En-têtes)
    Route::apiResource('expense-reports', ExpenseReportController::class);
    Route::post('expense-reports/{expense_report}/submit', [ExpenseReportController::class, 'submit'])
        ->name('expense-reports.submit');

    // Gestion des items (Lignes)
    Route::post('expense-items', [ExpenseItemController::class, 'store'])
        ->name('expense-items.store');
    Route::delete('expense-items/{expense_item}', [ExpenseItemController::class, 'destroy'])
        ->name('expense-items.destroy');

    // --- Routes Managers / Validateurs ---

    Route::middleware(['can:tenant.expenses.manage'])->group(function () {
        Route::patch('expense-reports/{expense_report}/status', [ExpenseApprovalController::class, 'updateStatus'])
            ->name('expense-reports.update-status');
    });
});
