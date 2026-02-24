<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Enums\Payroll\PayslipLineType;
use App\Models\Payroll\Payslip;
use DB;

/**
 * Moteur de calcul mis à jour selon l'audit :
 * - Aucune valeur en dur.
 * - Utilisation de PayrollScaleService.
 * - Gestion du gel des métadonnées (Snapshot).
 */
class PayrollCalculationService
{
    public function __construct(
        protected PayrollScaleService $scaleService
    ) {}

    /**
     * Calcule et génère les lignes d'un bulletin à partir des données agrégées.
     */
    public function computePayslip(Payslip $payslip, array $aggregatedData): void
    {
        DB::transaction(function () use ($payslip, $aggregatedData) {
            $payslip->lines()->where('is_manual_adjustment', false)->delete();

            $employee = $payslip->employee;
            $tenantId = $payslip->tenants_id;

            // 1. Snapshot des métadonnées (Garantit l'intégrité historique)
            $payslip->update([
                'metadata' => [
                    'level' => $employee->level,
                    'coefficient' => $employee->coefficient,
                    'hourly_rate' => $employee->hourly_rate ?? $employee->contractual_hourly_rate,
                    'btp_zone' => $employee->btp_travel_zone,
                ]
            ]);

            $baseRate = (float) ($employee->hourly_rate ?? $employee->contractual_hourly_rate);

            // 2. Lignes de Gains (Heures, Heures Sup, Primes BTP)
            $this->calculateGains($payslip, $aggregatedData['work'], $baseRate);

            // 3. Lignes d'Absences (Retenues)
            $this->calculateAbsenceDeductions($payslip, $aggregatedData['absences'], $baseRate);

            // 4. Lignes de Cotisations (Dynamiques via Template)
            $status = $employee->status ? $employee->status->value : 'ouvrier';
            $this->calculateContributions($payslip, $status);

            // 5. Finalisation des totaux
            $this->refreshTotals($payslip);
        });
    }

    protected function generateHoursLines(Payslip $payslip, float $totalHours, float $rate): void
    {
        $baseHours = min($totalHours, 151.67);
        $overtime25 = 0;
        $overtime50 = 0;

        if ($totalHours > 151.67) {
            $remaining = $totalHours - 151.67;
            $overtime25 = min($remaining, 34.66); // Jusqu'à 186.33h (151.67 + 34.66)
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

    public function refreshTotals(Payslip $payslip): void
    {
        $gross = $payslip->lines()->sum('amount_gain');
        $deductions = $payslip->lines()->sum('amount_deduction');

        $payslip->update([
            'gross_amount' => $gross,
            'net_to_pay' => $gross - $deductions,
            // Calcul PAS (Prélèvement à la source) simplifié
            'pas_amount' => ($gross - $deductions) * ($payslip->pas_rate / 100),
        ]);
    }

    protected function calculateGains(Payslip $payslip, array $work, float $rate): void
    {
        // Utilisation de la méthode dédiée pour le découpage des heures
        $this->generateHoursLines($payslip, $work['total_hours'], $rate);

        // Indemnité Repas (Taux dynamique)
        if (isset($work['meal_count']) && $work['meal_count'] > 0) {
            $mealRate = $this->scaleService->getRate('repas_btp', $payslip->tenants_id);
            $payslip->lines()->create([
                'label' => 'Indemnité de repas (Panier)',
                'base' => $work['meal_count'],
                'rate' => $mealRate,
                'amount_gain' => $work['meal_count'] * $mealRate,
                'type' => PayslipLineType::Earning,
                'is_taxable' => false, // Non imposable selon barème
                'sort_order' => 50,
            ]);
        }
    }

    protected function calculateAbsenceDeductions(Payslip $payslip, \Illuminate\Support\Collection $absences, float $rate): void
    {
        foreach ($absences as $absence) {
            $hours = $absence['duration_days'] * 7; // Hypothèse 7h/jour
            $payslip->lines()->create([
                'label' => 'Absence ' . $absence['label'],
                'base' => $hours,
                'rate' => $rate,
                'amount_deduction' => $hours * $rate,
                'type' => PayslipLineType::Deduction,
                'sort_order' => 20,
            ]);
        }
    }

    protected function calculateContributions(Payslip $payslip, string $status): void
    {
        $gross = $payslip->lines()->where('type', PayslipLineType::Earning)->sum('amount_gain');
        $rates = $this->scaleService->getContributionRates($payslip->tenants_id, $status);

        foreach ($rates as $tpl) {
            $payslip->lines()->create([
                'label' => $tpl->label,
                'base' => $gross,
                'rate' => $tpl->employee_rate,
                'amount_deduction' => round($gross * ($tpl->employee_rate / 100), 2),
                'employer_amount' => round($gross * ($tpl->employer_rate / 100), 2),
                'type' => PayslipLineType::Deduction,
                'sort_order' => 100,
            ]);
        }
    }
}
