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

        // 1. Calcul des fournitures (Vente et Coût)
        $totalSales = $intervention->items->where('is_billable', true)->sum('total_ht');
        $totalMaterialCost = $intervention->items->sum(function ($item) {
            return (float) $item->quantity * (float) $item->unit_cost_ht;
        });

        // 2. Calcul de la main d'œuvre (Basé sur le coût chargé des employés)
        $totalLaborCost = $intervention->technicians->sum(function ($employee) {
            return (float) $employee->pivot->hours_spent * (float) $employee->hourly_cost_charged;
        });

        $totalCost = $totalMaterialCost + $totalLaborCost;
        $margin = $totalSales - $totalCost;

        $intervention->update([
            'amount_ht' => $totalSales,
            'amount_cost_ht' => $totalCost,
            'margin_ht' => $totalSales - $totalCost,
        ]);

        if ($totalSales > 0 && ($margin / $totalSales) < 0.15) {
            $managers = User::permission('intervention.manage')->get();
            if ($managers->isNotEmpty()) {
                Notification::send($managers, new LowMarginAlertNotification($intervention));
            }
        }
    }
}
