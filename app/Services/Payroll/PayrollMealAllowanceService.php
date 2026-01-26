<?php

namespace App\Services\Payroll;

use App\Models\HR\Employee;
use App\Models\Payroll\MealAllowance;
use App\Models\Payroll\PayrollMealAllowance;
use App\Models\Payroll\PayrollSlip;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PayrollMealAllowanceService
{
    /**
     * Calcule les indemnités repas pour une fiche de paie
     */
    public function calculateForPayrollSlip(PayrollSlip $slip): array
    {
        $mealAllowances = $this->getActiveMealAllowances(
            $slip->employee,
            $slip->period_start,
            $slip->period_end,
        );

        $totalAmount = 0;
        $daysCount = 0;

        foreach ($mealAllowances as $allowance) {
            $daysInPeriod = $this->countWorkingDaysInPeriod(
                $slip->period_start,
                $slip->period_end,
                $allowance,
            );

            $amount = match ($allowance->type->value) {
                'forfeit' => $allowance->amount * $daysInPeriod,
                'per_day' => $allowance->amount * $daysInPeriod,
                default => 0,
            };

            $totalAmount += $amount;
            $daysCount += $daysInPeriod;
        }

        return [
            'total_amount' => $totalAmount,
            'days_count' => $daysCount,
            'allowances' => $mealAllowances,
        ];
    }

    /**
     * Récupère les indemnités repas actives pour un employé
     */
    private function getActiveMealAllowances(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): Collection {
        return MealAllowance::query()
            ->where('employee_id', $employee->id)
            ->where('tenant_id', $employee->tenant_id)
            ->where('is_active', true)
            ->where('start_date', '<=', $periodEnd)
            ->where(function ($query) use ($periodEnd) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $periodEnd);
            })
            ->get();
    }

    /**
     * Compte les jours ouvrables dans la période
     */
    private function countWorkingDaysInPeriod(
        Carbon $periodStart,
        Carbon $periodEnd,
        MealAllowance $allowance,
    ): int {
        $effectiveStart = $periodStart->copy();
        $effectiveEnd = $periodEnd->copy();

        // Respecter les dates de début/fin de l'indemnité
        if ($allowance->start_date > $effectiveStart) {
            $effectiveStart = $allowance->start_date;
        }
        if ($allowance->end_date && $allowance->end_date < $effectiveEnd) {
            $effectiveEnd = $allowance->end_date;
        }

        $days = 0;
        $current = $effectiveStart->copy();

        while ($current <= $effectiveEnd) {
            if ($current->isWeekday()) {
                $days++;
            }
            $current->addDay();
        }

        return $days;
    }

    /**
     * Crée les lignes de repas pour la fiche de paie
     */
    public function createMealAllowanceLines(
        PayrollSlip $slip,
        array $calculations,
    ): void {
        if ($calculations['total_amount'] <= 0) {
            return;
        }

        PayrollMealAllowance::create([
            'payroll_slip_id' => $slip->id,
            'amount' => $calculations['total_amount'],
            'days_count' => $calculations['days_count'],
            'description' => "Indemnité repas - {$calculations['days_count']} jours",
        ]);
    }
}
