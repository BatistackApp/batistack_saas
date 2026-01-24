<?php

namespace App\Services\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeRate;
use Carbon\Carbon;

class EmployeeService
{
    /**
     * Créer un nouvel employé avec ses données initiales
     */
    public function create(array $data): Employee
    {
        return Employee::create($data);
    }

    /**
     * Récupérer le taux horaire actif d'un employé pour une date donnée
     */
    public function getCurrentRate(Employee $employee, $date = null): ?EmployeeRate
    {
        $date = $date ?? now();

        return $employee->rates()
            ->where('effective_from', '<=', $date->toDateString())
            ->where(function ($query) use ($date) {
                $query->whereNull('effective_to')
                    ->orWhere('effective_to', '>=', $date->toDateString());
            })
            ->latest('effective_from')
            ->first();
    }

    /**
     * Créer/mettre à jour le taux horaire d'un employé
     */
    public function setRate(Employee $employee, float $hourlyRate, Carbon $effectiveFrom, ?Carbon $effectiveTo = null): EmployeeRate
    {
        // Clôturer le taux précédent
        $currentRate = $this->getCurrentRate($employee, $effectiveFrom->subDay());
        if ($currentRate && $currentRate->effective_to === null) {
            $currentRate->update(['effective_to' => $effectiveFrom->subDay()]);
        }

        return $employee->rates()->create([
            'hourly_rate' => $hourlyRate,
            'effective_from' => $effectiveFrom,
            'effective_to' => $effectiveTo,
        ]);
    }

    /**
     * Récupérer l'historique des taux d'un employé
     */
    public function getRatesHistory(Employee $employee): \Illuminate\Database\Eloquent\Collection
    {
        return $employee->rates()
            ->orderByDesc('effective_from')
            ->get();
    }

    /**
     * Désactiver un employé
     */
    public function deactivate(Employee $employee): void
    {
        $employee->update([
            'status' => false,
            'resignation_date' => now(),
        ]);
    }

    /**
     * Réactiver un employé
     */
    public function reactivate(Employee $employee): void
    {
        $employee->update([
            'status' => true,
            'resignation_date' => null,
        ]);
    }
}
