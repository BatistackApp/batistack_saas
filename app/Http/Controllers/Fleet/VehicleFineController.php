<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleFineRequest;
use App\Models\Fleet\VehicleFine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleFineController extends Controller
{
    /**
     * Liste paginée des contraventions du parc.
     */
    public function index(Request $request): JsonResponse
    {
        $fines = VehicleFine::with(['vehicle', 'driver'])
            ->when($request->vehicle_id, fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest('offense_at')
            ->paginate(20);

        return response()->json($fines);
    }

    /**
     * Enregistrement d'un nouvel avis de contravention.
     */
    public function store(VehicleFineRequest $request): JsonResponse
    {
        $fine = VehicleFine::create($request->validated());

        return response()->json([
            'message' => 'Contravention enregistrée avec succès.',
            'data' => $fine,
        ], 201);
    }

    /**
     * Détails d'une contravention spécifique.
     */
    public function show(VehicleFine $fine): JsonResponse
    {
        return response()->json($fine->load(['vehicle', 'user']));
    }

    /**
     * Mise à jour des informations (ex: attribution d'un chauffeur a posteriori).
     */
    public function update(VehicleFineRequest $request, VehicleFine $fine): JsonResponse
    {
        $fine->update($request->validated());

        return response()->json([
            'message' => 'Contravention mise à jour.',
            'data' => $fine,
        ]);
    }

    /**
     * Suppression d'une contravention (si saisie par erreur).
     */
    public function destroy(VehicleFine $fine): JsonResponse
    {
        $fine->delete();

        return response()->json(['message' => 'Contravention supprimée.']);
    }
}
