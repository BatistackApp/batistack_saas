<?php

namespace App\Http\Controllers\Pilotage;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pilotage\KpiIndicatorRequest;
use App\Models\Pilotage\KpiIndicator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KpiIndicatorController extends Controller
{
    /**
     * Liste des indicateurs du tenant.
     */
    public function index(): JsonResponse
    {
        $indicators = KpiIndicator::withCount('thresholds')
            ->orderBy('category')
            ->get();

        return response()->json($indicators);
    }

    /**
     * Enregistre un nouvel indicateur.
     */
    public function store(KpiIndicatorRequest $request): JsonResponse
    {
        $indicator = KpiIndicator::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        return response()->json($indicator, 201);
    }

    /**
     * Récupère l'historique des valeurs pour un graphique.
     */
    public function history(KpiIndicator $indicator, Request $request): JsonResponse
    {
        $days = $request->query('days', 30);

        $snapshots = $indicator->snapshots()
            ->where('measured_at', '>=', now()->subDays($days))
            ->orderBy('measured_at', 'asc')
            ->get(['value', 'measured_at']);

        return response()->json([
            'indicator' => $indicator->name,
            'unit' => $indicator->unit,
            'data' => $snapshots,
        ]);
    }
}
