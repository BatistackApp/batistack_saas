<?php

use App\Http\Controllers\Expense\ExpenseApprovalController;
use App\Http\Controllers\Expense\ExpenseItemController;
use App\Http\Controllers\Expense\ExpenseReportController;

Route::prefix('expense')->group(function () {
    // --- Espace Salarié (Self-service) ---
    Route::apiResource('reports', ExpenseReportController::class)->names([
        'index' => 'expenses.reports.index',
        'store' => 'expenses.reports.store',
        'show' => 'expenses.reports.show',
        'update' => 'expenses.reports.update',
        'destroy' => 'expenses.reports.destroy',
    ]);

    Route::post('reports/{expense_report}/submit', [ExpenseReportController::class, 'submit'])
        ->name('expenses.reports.submit');

    // Gestion des lignes (Items)
    Route::post('items', [ExpenseItemController::class, 'store'])->name('expenses.items.store');
    Route::delete('items/{expense_item}', [ExpenseItemController::class, 'destroy'])->name('expenses.items.destroy');

    // --- Espace Manager & Compta (Validation) ---
    Route::middleware(['can:tenant.expenses.validate'])->group(function () {
        Route::get('pending', [ExpenseApprovalController::class, 'pending'])
            ->name('expenses.approval.pending');

        Route::patch('reports/{expense_report}/status', [ExpenseApprovalController::class, 'updateStatus'])
            ->name('expenses.reports.update-status');
    });
});
