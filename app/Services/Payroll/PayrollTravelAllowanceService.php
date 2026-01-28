<?php

namespace App\Services\Payroll;

use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollTravelAllowances;
use App\Models\Payroll\PayrollTravelAllowanceSetting;

class PayrollTravelAllowanceService
{
    /**
     * Calcule les indemnités de trajet pour une fiche de paie
     */
    public function calculateForPayrollSlip(
        PayrollSlip $slip,
        ?float $distanceKm = null,
    ): array {
        $setting = PayrollTravelAllowanceSetting::firstWhere(
            'tenant_id',
            $slip->tenant_id,
        );

        if (!$setting || !$setting->is_active) {
            return ['total_amount' => 0, 'distance_km' => 0];
        }

        $amount = match ($setting->type->value) {
            'kilometre' => $this->calculateByKilometre($distanceKm, $setting),
            'forfeit' => $this->calculateByForfeit($setting),
            default => 0,
        };

        return [
            'total_amount' => $amount,
            'distance_km' => $distanceKm ?? 0,
            'setting' => $setting,
        ];
    }

    /**
     * Calcule selon le tarif au kilomètre
     */
    private function calculateByKilometre(
        ?float $distanceKm,
        PayrollTravelAllowanceSetting $setting,
    ): float {
        if (!$distanceKm || $distanceKm <= 0) {
            return 0;
        }

        $amount = $distanceKm * $setting->rate_per_km;

        // Limiter au montant maximum par jour si configuré
        if ($setting->max_amount_per_day > 0) {
            $amount = min($amount, $setting->max_amount_per_day);
        }

        return round($amount, 2);
    }

    /**
     * Calcule selon le forfait fixe
     */
    private function calculateByForfeit(
        PayrollTravelAllowanceSetting $setting,
    ): float {
        return $setting->forfeit_amount ?? 0;
    }

    /**
     * Crée les lignes de trajet pour la fiche de paie
     */
    public function createTravelAllowanceLines(
        PayrollSlip $slip,
        array $calculations,
    ): void {
        if ($calculations['total_amount'] <= 0) {
            return;
        }

        $description = $calculations['distance_km'] > 0
            ? "Indemnité trajet - {$calculations['distance_km']} km"
            : "Indemnité trajet forfaitaire";

        PayrollTravelAllowances::create([
            'payroll_slip_id' => $slip->id,
            'amount' => $calculations['total_amount'],
            'distance_km' => $calculations['distance_km'] ?: null,
            'description' => $description,
        ]);
    }

    /**
     * Récupère ou crée la configuration de trajet pour un tenant
     */
    public function getOrCreateSetting($tenantId): PayrollTravelAllowanceSetting
    {
        return PayrollTravelAllowanceSetting::firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'type' => 'forfeit',
                'forfeit_amount' => 0,
                'is_active' => false,
            ]
        );
    }
}
