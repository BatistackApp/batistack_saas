<?php

namespace App\Services\Pilotage;

use App\Models\Pilotage\KpiIndicator;
use App\Models\Pilotage\KpiSnapshot;

class SnapshotOrchestrator
{
    public function __construct(
        protected KpiAggregationService $aggregationService,
        protected AlertManagerService $alertManager
    ) {}

    /**
     * Génère les snapshots pour tous les indicateurs actifs d'un tenant.
     */
    public function takeGlobalSnapshots(int $tenantId): void
    {
        $indicators = KpiIndicator::where('tenants_id', $tenantId)
            ->where('is_active', true)
            ->get();

        foreach ($indicators as $indicator) {
            $value = $this->calculateValue($indicator);

            $snapshot = KpiSnapshot::create([
                'tenants_id' => $tenantId,
                'kpi_indicator_id' => $indicator->id,
                'value' => $value,
                'measured_at' => now(),
            ]);

            // Vérification immédiate des seuils
            $this->alertManager->checkThresholds($snapshot);
        }
    }

    /**
     * Logique de routage vers la formule de calcul appropriée.
     */
    protected function calculateValue(KpiIndicator $indicator, $context = null): string
    {
        return match ($indicator->code) {
            'net_cash' => $this->aggregationService->getNetCash($indicator->tenants_id),
            'global_margin' => '0', // À implémenter : marge consolidée
            default => '0',
        };
    }
}
