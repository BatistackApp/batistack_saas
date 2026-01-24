<?php

namespace App\Services\HR;

use App\Models\Chantiers\Chantier;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class ChantiersLaborCostService
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    /**
     * Calculer le coût total de la main-d'œuvre pour un chantier sur une période
     */
    public function calculateChantieLaborCost(Chantier $chantier, Carbon $startDate, Carbon $endDate): array
    {
        $timesheetLines = $chantier->timesheetLines()
            ->whereHas('employeeTimesheet', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('timesheet_date', [$startDate, $endDate])
                    ->where('status', 'validated');
            })
            ->with(['employeeTimesheet.employee', 'employeeTimesheet.employee.rates'])
            ->get();

        $costByEmployee = [];
        $totalCost = 0;

        foreach ($timesheetLines as $line) {
            $employee = $line->employeeTimesheet->employee;
            $rate = $this->employeeService->getCurrentRate($employee, $line->employeeTimesheet->timesheet_date);

            if (! $rate) {
                continue;
            }

            $lineCost = ($line->hours_work + $line->hours_travel) * $rate->hourly_rate;
            $totalCost += $lineCost;

            if (! isset($costByEmployee[$employee->id])) {
                $costByEmployee[$employee->id] = [
                    'employee' => $employee,
                    'hours_work' => 0,
                    'hours_travel' => 0,
                    'hourly_rate' => $rate->hourly_rate,
                    'cost' => 0,
                ];
            }

            $costByEmployee[$employee->id]['hours_work'] += $line->hours_work;
            $costByEmployee[$employee->id]['hours_travel'] += $line->hours_travel;
            $costByEmployee[$employee->id]['cost'] += $lineCost;
        }

        return [
            'chantier' => $chantier,
            'period' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'total_cost' => $totalCost,
            'cost_by_employee' => $costByEmployee,
        ];
    }

    /**
     * Récupérer les heures totales pointées sur un chantier pour une période
     */
    public function getChantieTotalHours(Chantier $chantier, Carbon $startDate, Carbon $endDate): array
    {
        $timesheetLines = $chantier->timesheetLines()
            ->whereHas('employeeTimesheet', function ($query) use ($startDate, $endDate) {
                $query->whereBetween('timesheet_date', [$startDate, $endDate])
                    ->where('status', 'validated');
            })
            ->get();

        return [
            'total_hours_work' => $timesheetLines->sum('hours_work'),
            'total_hours_travel' => $timesheetLines->sum('hours_travel'),
            'total_hours' => $timesheetLines->sum(function ($line) {
                return $line->hours_work + $line->hours_travel;
            }),
        ];
    }

    /**
     * Obtenir tous les chantiers avec leurs coûts de main-d'œuvre pour une période
     */
    public function getChantiersCostsReport(Carbon $startDate, Carbon $endDate): \Illuminate\Support\Collection
    {
        $chantiers = Chantier::all();
        $report = [];

        foreach ($chantiers as $chantier) {
            $report[] = $this->calculateChantieLaborCost($chantier, $startDate, $endDate);
        }

        return collect($report);
    }
}
