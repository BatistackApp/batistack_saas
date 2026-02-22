<?php

namespace App\Services\Intervention;

use App\Models\Intervention\Intervention;
use App\Models\User;
use App\Notifications\Intervention\LowMarginAlertNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Service de gestion financière (Marge et Totaux).
 */
class InterventionFinancialService
{
    /**
     * Calcule et met à jour les totaux et la marge de l'intervention.
     */
    public function refreshValuation(Intervention $intervention): void
    {
        $intervention->load(['items', 'technicians']);

        // 1. Calcul des fournitures (Vente et Coût de revient matériel)
        $totalSales = $intervention->items->where('is_billable', true)->sum('total_ht');

        $totalMaterialCost = $intervention->items->sum(function ($item) {
            return (float) ($item->quantity * $item->unit_cost_ht);
        });

        // 2. Calcul de la main d'œuvre (Basé sur le coût chargé des employés)
        $totalLaborCost = $intervention->technicians->sum(function ($employee) {
            return (float) ($employee->pivot->hours_spent * ($employee->hourly_cost_charged ?? 0));
        });

        $margin = $totalSales - ($totalMaterialCost + $totalLaborCost);

        // Mise à jour avec distinction des colonnes (Recommandation BTP)
        $intervention->update([
            'amount_ht' => $totalSales,
            'material_cost_ht' => $totalMaterialCost,
            'labor_cost_ht' => $totalLaborCost,
            'margin_ht' => $margin,
        ]);

        // Alerte si la marge est trop faible (< 15%)
        if ($totalSales > 0 && ($margin / $totalSales) < 0.15) {
            $managers = User::permission('intervention.manage')->get();
            if ($managers->isNotEmpty()) {
                Notification::send($managers, new LowMarginAlertNotification($intervention, $margin));
            }
        }
    }
}
