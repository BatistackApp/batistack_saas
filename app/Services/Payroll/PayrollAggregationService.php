<?php

namespace App\Services\Payroll;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;

class PayrollAggregationService
{
    /**
     * Récupère toutes les variables de paie approuvées pour la période.
     */
    public function getAggregatedData(Employee $employee, PayrollPeriod $period): array
    {
        // 1. Récupération des Heures de travail (via TimeEntries approuvées)
        $entries = $employee->timeEntries()
            ->whereBetween('date', [$period->start_date, $period->end_date])
            ->where('status', TimeEntryStatus::Approved)
            ->get();

        // 2. Récupération des Absences (via AbsenceService / AbsenceRequest)
        // Note: On utilise les requêtes approuvées qui chevauchent la période
        $absences = $employee->absenceRequests()
            ->where('status', AbsenceRequestStatus::Approved)
            ->where(function ($query) use ($period) {
                $query->whereBetween('starts_at', [$period->start_date, $period->end_date])
                    ->orWhereBetween('ends_at', [$period->start_date, $period->end_date]);
            })
            ->get();

        return [
            'work' => [
                'total_hours' => (float) $entries->sum('hours'),
                'meal_count' => $entries->where('has_meal_allowance', true)->count(),
                'travel_zones' => $entries->groupBy('btp_travel_zone')->map->count(),
                'projects_breakdown' => $entries->groupBy('project_id')->map(fn ($group) => [
                    'hours' => $group->sum('hours'),
                    'project_id' => $group->first()->project_id,
                ]),
            ],
            'absences' => $absences->map(fn ($a) => [
                'type' => $a->type,
                'duration_days' => $a->duration_days, // Déjà calculé par AbsenceService
                'label' => $a->type->getLabel(),
            ]),
        ];
    }
}
