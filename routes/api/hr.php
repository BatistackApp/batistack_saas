<?php

use App\Http\Controllers\Core\HolidayController;
use App\Http\Controllers\HR\AbsenceRequestController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\TimeEntryController;

Route::prefix('hr')->group(function () {
    // --- Employés ---
    Route::apiResource('employees', EmployeeController::class);
    Route::get('employees/{employee}/time-entries', [TimeEntryController::class, 'indexByEmployee'])
        ->name('employees.time-entries');

    // --- Pointages Chantier ---
    Route::apiResource('time-entries', TimeEntryController::class);
    Route::patch('time-entries/{timeEntry}/verify', [TimeEntryController::class, 'verify'])
        ->name('time-entries.verify');

    // --- Gestion des Absences ---
    Route::apiResource('absences', AbsenceRequestController::class)->except(['update']);
    Route::patch('absences/{absenceRequest}/review', [AbsenceRequestController::class, 'review'])
        ->name('absences.review');

    // --- Configuration Jours Fériés (Tenant) ---
    Route::get('holidays', [HolidayController::class, 'index'])->name('holidays.index');
    Route::post('holidays', [HolidayController::class, 'store'])->name('holidays.store');
    Route::post('holidays/sync', [HolidayController::class, 'sync'])->name('holidays.sync');
    Route::delete('holidays/{holiday}', [HolidayController::class, 'destroy'])->name('holidays.destroy');
});
