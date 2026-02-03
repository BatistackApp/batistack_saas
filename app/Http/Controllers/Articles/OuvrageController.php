<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\OuvrageConsumptionRequest;
use App\Http\Requests\Articles\OuvrageRequest;
use App\Models\Articles\Ouvrage;
use App\Models\Articles\Warehouse;
use App\Services\Articles\StockMovementService;
use Illuminate\Http\JsonResponse;

class OuvrageController extends Controller
{
    public function __construct(
        protected StockMovementService $stockService
    ) {}

    public function index(): JsonResponse
    {
        $ouvrages = Ouvrage::with('components')
            ->latest()
            ->paginate(15);

        // On ajoute le coût théorique calculé dynamiquement pour chaque ouvrage
        $ouvrages->getCollection()->transform(function ($ouvrage) {
            return [
                'id' => $ouvrage->id,
                'sku' => $ouvrage->sku,
                'name' => $ouvrage->name,
                'unit' => $ouvrage->unit,
                'theoretical_cost' => $ouvrage->theoretical_cost, // Accessor défini dans le modèle
                'is_active' => $ouvrage->is_active,
                'components_count' => $ouvrage->components->count(),
            ];
        });

        return response()->json($ouvrages);
    }

    public function store(OuvrageRequest $request): JsonResponse
    {
        $ouvrage = Ouvrage::create($request->validated());

        // Si des composants sont fournis dans la requête
        if ($request->has('components')) {
            foreach ($request->components as $component) {
                $ouvrage->components()->attach($component['article_id'], [
                    'quantity_needed' => $component['quantity_needed']
                ]);
            }
        }

        return response()->json($ouvrage->load('components'), 201);
    }

    public function show(Ouvrage $ouvrage): JsonResponse
    {
        return response()->json($ouvrage->load('components'));
    }

    /**
     * CONSOMMATION CHANTIER (Explosion de nomenclature)
     * C'est ici que la magie opère : une saisie d'ouvrage déclenche
     * les sorties de stock de tous les composants.
     */
    public function consume(OuvrageConsumptionRequest $request): JsonResponse
    {
        try {
            $ouvrage = Ouvrage::findOrFail($request->ouvrage_id);
            $warehouse = Warehouse::findOrFail($request->warehouse_id);

            // Appel au service pour gérer l'explosion atomique
            $movements = $this->stockService->recordOuvrageExit(
                $ouvrage,
                $warehouse,
                (float) $request->quantity,
                $request->project_id,
                [
                    'reference' => $request->reference,
                    'notes' => $request->notes
                ]
            );

            return response()->json([
                'message' => "L'ouvrage {$ouvrage->name} a été consommé avec succès.",
                'movements_count' => count($movements),
                'movements' => $movements
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => "Erreur lors de la consommation de l'ouvrage.",
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function update(OuvrageRequest $request, Ouvrage $ouvrage): JsonResponse
    {
        $ouvrage->update($request->validated());

        if ($request->has('components')) {
            // On synchronise la nomenclature
            $syncData = [];
            foreach ($request->components as $component) {
                $syncData[$component['article_id']] = ['quantity_needed' => $component['quantity_needed']];
            }
            $ouvrage->components()->sync($syncData);
        }

        return response()->json($ouvrage->load('components'));
    }

    public function destroy(Ouvrage $ouvrage): JsonResponse
    {
        $ouvrage->delete();
        return response()->json(null, 204);
    }
}
