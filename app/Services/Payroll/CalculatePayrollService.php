<?php

namespace App\Services\Payroll;

use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollSetting;
use Illuminate\Support\Collection;

class CalculatePayrollService
{
    /**
     * @param Employee $employee
     * @param Tenant $company
     * @param Collection $timesheets
     * @return array<string, mixed>
     */
    public function calculate(
        Employee $employee,
        Tenant $company,
        Collection $timesheets,
    ): array {
        $settings = PayrollSetting::firstWhere('company_id', $company->id);
        $hourlyRate = $employee->hourly_rate ?? 20.00;

        // Grouper par chantier
        $groupedByChantier = $this->groupByChantier($timesheets);

        // Calculer les heures totales
        $totalHoursWork = $groupedByChantier->sum('hours_work');
        $totalHoursTravel = $groupedByChantier->sum('hours_travel');

        // Calculer les montants
        $grossAmount = ($totalHoursWork * $hourlyRate);
        $transportAmount = $this->calculateTransportAmount($employee);
        $grossWithTransport = $grossAmount + $transportAmount;

        $contributionRate = $settings?->social_contribution_rate ?? 42.00;
        $socialContributions = ($grossWithTransport * $contributionRate) / 100;

        $netAmount = $grossWithTransport - $socialContributions;

        return [
            'total_hours_work' => $totalHoursWork,
            'total_hours_travel' => $totalHoursTravel,
            'gross_amount' => $grossAmount,
            'transport_amount' => $transportAmount,
            'social_contributions' => $socialContributions,
            'employee_deductions' => 0,
            'net_amount' => $netAmount,
            'lines' => $this->createLines($groupedByChantier, $hourlyRate),
        ];
    }

    /**
     * @param Collection $timesheets
     * @return Collection<string, mixed>
     */
    private function groupByChantier(Collection $timesheets): Collection
    {
        return $timesheets->groupBy(fn ($entry) => $entry->chantier_id)
            ->map(function (Collection $entries) {
                return [
                    'chantier_id' => $entries->first()->chantier_id,
                    'chantier' => $entries->first()->chantier,
                    'hours_work' => $entries->where('type', 'work')->sum('hours'),
                    'hours_travel' => $entries->where('type', 'travel')->sum('hours'),
                ];
            });
    }

    private function calculateTransportAmount(Employee $employee): float
    {
        if (! $employee->has_transport_benefit) {
            return 0.0;
        }

        return $employee->transport_allowance ?? 0.0;
    }

    /**
     * @param Collection $groupedByChantier
     * @param float $hourlyRate
     * @return array<int, array<string, mixed>>
     */
    private function createLines(
        Collection $groupedByChantier,
        float $hourlyRate,
    ): array {
        return $groupedByChantier->map(function (array $group) use ($hourlyRate) {
            $totalHours = $group['hours_work'] + $group['hours_travel'];
            $amount = $totalHours * $hourlyRate;

            return [
                'chantier_id' => $group['chantier_id'],
                'description' => $group['chantier']?->name ?? 'Non imputé',
                'hours_work' => $group['hours_work'],
                'hours_travel' => $group['hours_travel'],
                'hourly_rate' => $hourlyRate,
                'amount' => $amount,
            ];
        })->values()->toArray();
    }
}
