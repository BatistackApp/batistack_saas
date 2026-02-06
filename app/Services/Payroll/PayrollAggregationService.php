<?php

namespace App\Services\Payroll;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;

class PayrollAggregationService
{
    /**
     * Récupère les données de pointage approuvées pour un employé sur une période.
     */
    public function getAggregatedTimeData(Employee $employee, PayrollPeriod $period): array
    {
        $entries = $employee->timeEntries()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->where('status', TimeEntryStatus::Approved) // Statut du module RH-Pointage
            ->get();

        $totalHours = (float) $entries->sum('hours');
        $mealAllowancesCount = $entries->where('has_meal_allowance', true)->count();
        $travelTime = (float) $entries->sum('travel_time');

        return [
            'total_hours' => $totalHours,
            'meal_count' => $mealAllowancesCount,
            'travel_time' => $travelTime,
            'entries' => $entries
        ];
    }
}
