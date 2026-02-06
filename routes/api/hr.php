<?php

use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\TimeEntryController;

Route::prefix('hr')->group(function () {
    // Routes pour les employés
    Route::apiResource('employees', EmployeeController::class);

    // Routes pour les pointages
    Route::apiResource('time-entries', TimeEntryController::class);

    // Route spécifique pour la vérification des temps (Approbation)
    Route::patch('time-entries/{timeEntry}/verify', [TimeEntryController::class, 'verify'])
        ->name('time-entries.verify');

    // Route pour récupérer les pointages d'un employé spécifique (optionnel mais utile)
    Route::get('employees/{employee}/time-entries', function (\App\Models\HR\Employee $employee) {
        return response()->json($employee->timeEntries()->latest()->get());
    })->name('employees.time-entries');
});
