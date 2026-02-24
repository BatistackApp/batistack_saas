<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\PayrollPeriod;
use DB;

/**
 * Ventile les coûts salariaux (Brut + Charges) sur les budgets projets.
 * Utilise les données d'agrégation pour savoir quel temps a été passé où.
 */
class PayrollImputationService
{
    public function dispatchToProjects(PayrollPeriod $period): void
    {
        foreach ($period->payslips as $payslip) {
            $aggregated = app(PayrollAggregationService::class)->getAggregatedData($payslip->employee, $period);

            // Calcul du coût total "Chargé" du bulletin
            $totalGross = $payslip->gross_amount;
            $totalEmployerCharges = $payslip->lines()
                ->where('type', PayslipLineType::Deduction)
                ->sum('employer_amount');

            $fullCost = $totalGross + $totalEmployerCharges;
            $totalHours = $aggregated['work']['total_hours'];

            if ($totalHours <= 0) {
                continue;
            }

            $hourlyCostCharged = $fullCost / $totalHours;

            // Ventilation par projet
            foreach ($aggregated['work']['projects_breakdown'] as $breakdown) {
                $projectCost = $breakdown['hours'] * $hourlyCostCharged;

                // Ici on interagit avec le module Projet
                // On peut imaginer un ProjectCost::create(...) ou un job dédié
                DB::table('project_imputations')->insert([
                    'project_id' => $breakdown['project_id'],
                    'employee_id' => $payslip->employee_id,
                    'payroll_period_id' => $period->id,
                    'type' => 'payroll',
                    'amount' => round($projectCost, 2),
                    'created_at' => now(),
                    'metadata' => [
                        'hours' => $breakdown['hours'],
                    ],
                ]);
            }
        }
    }
}
