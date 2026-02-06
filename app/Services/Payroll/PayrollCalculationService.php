<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\Payslip;
use DB;

/**
 * Moteur de calcul des bulletins de paie (Brut, Net, Cotisations).
 */
class PayrollCalculationService
{
    /**
     * Calcule et génère les lignes d'un bulletin à partir des données agrégées.
     */
    public function computePayslip(Payslip $payslip, array $aggregatedData): void
    {
        DB::transaction(function () use ($payslip, $aggregatedData) {
            // 1. Nettoyage des anciennes lignes
            $payslip->lines()->where('is_manual_adjustment', false)->delete();

            $employee = $payslip->employee;

            // Note: On assume que l'employé a un 'hourly_rate' contractuel (différent du cost_charged)
            $hourlyRate = (float) ($employee->hourly_rate ?? 13.00);

            // 2. Calcul du Salaire de base et Heures Sup (Logique BTP)
            $this->generateHoursLines($payslip, $aggregatedData['total_hours'], $hourlyRate);

            // 3. Paniers Repas
            if ($aggregatedData['meal_count'] > 0) {
                $payslip->lines()->create([
                    'label' => 'Indemnité de repas',
                    'base' => $aggregatedData['meal_count'],
                    'rate' => 1.20, // Taux exemple
                    'amount_gain' => $aggregatedData['meal_count'] * 1.20,
                    'type' => PayslipLineType::Earning,
                    'sort_order' => 10,
                ]);
            }

            // 4. Calcul du Brut Total
            $gross = $payslip->lines()->where('type', PayslipLineType::Earning)->sum('amount_gain');

            // 5. Calcul des cotisations (Simpli-conceptuel ici)
            // Dans une version réelle, on bouclerait sur une table 'contribution_rates'
            $this->generateContributionLines($payslip, $gross);

            // 6. Mise à jour de l'en-tête du bulletin
            $payslip->update([
                'gross_amount' => $gross,
                'net_to_pay' => $this->calculateNetToPay($payslip),
                'status' => PayrollStatus::Draft
            ]);
        });
    }

    protected function generateHoursLines(Payslip $payslip, float $totalHours, float $rate): void
    {
        $baseHours = min($totalHours, 151.67);
        $overtime25 = 0;
        $overtime50 = 0;

        if ($totalHours > 151.67) {
            $remaining = $totalHours - 151.67;
            $overtime25 = min($remaining, 34.66); // Jusqu'à 186.33h
            $overtime50 = max(0, $remaining - 34.66);
        }

        // Ligne Base
        $payslip->lines()->create([
            'label' => 'Salaire de base',
            'base' => $baseHours,
            'rate' => $rate,
            'amount_gain' => round($baseHours * $rate, 2),
            'type' => PayslipLineType::Earning,
            'sort_order' => 1,
        ]);

        // Ligne 25%
        if ($overtime25 > 0) {
            $payslip->lines()->create([
                'label' => 'Heures mensuelles majorées 25%',
                'base' => $overtime25,
                'rate' => $rate * 1.25,
                'amount_gain' => round($overtime25 * $rate * 1.25, 2),
                'type' => PayslipLineType::Earning,
                'sort_order' => 2,
            ]);
        }

        // Ligne 50%
        if ($overtime50 > 0) {
            $payslip->lines()->create([
                'label' => 'Heures mensuelles majorées 50%',
                'base' => $overtime50,
                'rate' => $rate * 1.50,
                'amount_gain' => round($overtime50 * $rate * 1.50, 2),
                'type' => PayslipLineType::Earning,
                'sort_order' => 3,
            ]);
        }
    }

    protected function generateContributionLines(Payslip $payslip, float $gross): void
    {
        // Exemple: Retraite PRO BTP (Part salariale)
        $payslip->lines()->create([
            'label' => 'Retraite Complémentaire PRO BTP',
            'base' => $gross,
            'rate' => 0.04, // 4%
            'amount_deduction' => round($gross * 0.04, 2),
            'employer_amount' => round($gross * 0.06, 2), // Part patronale pour imputation chantier
            'type' => PayslipLineType::Deduction,
            'sort_order' => 100,
        ]);
    }

    public function calculateNetToPay(Payslip $payslip): float
    {
        $gains = $payslip->lines()->sum('amount_gain');
        $deductions = $payslip->lines()->sum('amount_deduction');
        return round($gains - $deductions, 2);
    }
}
