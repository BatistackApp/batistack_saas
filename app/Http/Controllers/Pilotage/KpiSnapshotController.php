<?php

namespace App\Http\Controllers\Pilotage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilotage\KpiManualSnapshotRequest;
use App\Services\Pilotage\SnapshotOrchestrator;
use Illuminate\Http\JsonResponse;

class KpiSnapshotController extends Controller
{
    public function __construct(
        protected SnapshotOrchestrator $orchestrator
    ) {}

    /**
     * Force la capture des indicateurs maintenant.
     */
    public function trigger(KpiManualSnapshotRequest $request): JsonResponse
    {
        $tenantId = auth()->user()->tenants_id;

        // On peut lancer l'orchestrateur de manière synchrone pour le feedback UI
        $this->orchestrator->takeGlobalSnapshots($tenantId);

        return response()->json([
            'message' => 'Les indicateurs ont été recalculés et archivés avec succès.'
        ]);
    }
}
