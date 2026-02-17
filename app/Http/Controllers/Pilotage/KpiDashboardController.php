<?php

namespace App\Http\Controllers\Pilotage;

use App\Http\Controllers\Controller;
use App\Services\Pilotage\KpiAggregationService;
use Illuminate\Http\JsonResponse;

class KpiDashboardController extends Controller
{
    public function __construct(
        protected KpiAggregationService $aggregationService
    ) {}

    /**
     * Retourne les indicateurs clés "temps réel" pour le résumé.
     */
    public function summary(): JsonResponse
    {
        $tenantId = auth()->user()->tenants_id;

        // Exemple de données agrégées à la volée
        return response()->json([
            'net_cash' => [
                'value' => $this->aggregationService->getNetCash($tenantId),
                'label' => 'Trésorerie Nette',
                'trend' => '+5.2%', // À calculer dynamiquement via snapshots
            ],
            // On pourrait ajouter d'autres calculs ici
        ]);
    }
}
