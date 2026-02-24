<?php

namespace App\Services\Payroll;

use DB;

/**
 * Gère la récupération dynamique des taux et barèmes BTP.
 */
class PayrollScaleService
{
    /**
     * Récupère un taux spécifique (ex: repas_btp, trajet_zone_1).
     */
    public function getRate(string $slug, int $tenantId, ?string $level = null, ?int $year = null): float
    {
        $year = $year ?? now()->year;

        $query = DB::table('payroll_scales')
            ->where('tenants_id', $tenantId)
            ->where('slug', $slug)
            ->where('active_year', $year);

        if ($level) {
            $query->where('employee_level', $level);
        }

        $scale = $query->first();

        // Fallback ou levée d'exception si le barème n'est pas configuré
        return $scale ? (float) $scale->rate : 0.0;
    }

    /**
     * Récupère le barème complet des cotisations pour un statut (Ouvrier/ETAM/Cadre).
     */
    public function getContributionRates(int $tenantId, string $employeeStatus): \Illuminate\Support\Collection
    {
        return DB::table('payroll_contribution_templates')
            ->where('tenants_id', $tenantId)
            ->where('applies_to_status', $employeeStatus)
            ->where('is_active', true)
            ->get();
    }
}
