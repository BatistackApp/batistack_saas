<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\MaintenancePlanRequest;
use App\Models\Fleet\VehicleMaintenancePlan;
use Illuminate\Http\JsonResponse;

class MaintenancePlanController extends Controller
{
    /**
     * Liste des plans de maintenance (modèles de seuils).
     */
    public function index(): JsonResponse
    {
        $plans = VehicleMaintenancePlan::latest()->get();

        return response()->json($plans);
    }

    /**
     * Création d'un nouveau plan (ex: Révision 1000h).
     */
    public function store(MaintenancePlanRequest $request): JsonResponse
    {
        $plan = VehicleMaintenancePlan::create($request->validated());

        return response()->json($plan, 201);
    }

    /**
     * Détails d'un plan spécifique.
     */
    public function show(VehicleMaintenancePlan $maintenance_plan): JsonResponse
    {
        return response()->json($maintenance_plan);
    }

    /**
     * Mise à jour d'un plan.
     */
    public function update(MaintenancePlanRequest $request, VehicleMaintenancePlan $maintenance_plan): JsonResponse
    {
        $maintenance_plan->update($request->validated());

        return response()->json($maintenance_plan);
    }

    /**
     * Suppression d'un plan (Soft Delete).
     */
    public function destroy(VehicleMaintenancePlan $maintenance_plan): JsonResponse
    {
        $maintenance_plan->delete();

        return response()->json(null, 204);
    }
}
