<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleRequest;
use App\Models\Fleet\Vehicle;
use App\Services\Fleet\FleetConsumptionService;
use App\Services\Fleet\FleetTollService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    /**
     * Liste paginée des véhicules du parc.
     */
    public function index(Request $request): JsonResponse
    {
        $vehicles = Vehicle::with(['currentAssignment.project', 'currentAssignment.user'])
            ->when($request->type, fn ($q) => $q->where('type', $request->type))
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('internal_code', 'like', "%{$request->search}%")
                    ->orWhere('license_plate', 'like', "%{$request->search}%");
            })
            ->where('tenants_id', auth()->user()->tenants_id)
            ->latest()
            ->paginate(15);

        return response()->json($vehicles);
    }

    /**
     * Création d'un nouveau véhicule.
     */
    public function store(VehicleRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenants_id'] = auth()->user()->tenants_id;
        $vehicle = Vehicle::create($data);

        return response()->json($vehicle, 201);
    }

    /**
     * Détails d'un véhicule incluant les compteurs et l'état de conformité.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        return response()->json($vehicle->load([
            'assignments.project',
            'inspections',
            'consumptions' => fn ($q) => $q->latest()->limit(10),
        ]));
    }

    /**
     * Mise à jour des informations techniques.
     */
    public function update(VehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        $vehicle->update($request->validated());

        return response()->json($vehicle);
    }

    /**
     * DÉCLENCHEMENT MANUEL DE LA SYNCHRONISATION API
     * Permet à l'utilisateur de forcer la récupération des données Carburant/Péage.
     */
    public function syncApi(Vehicle $vehicle, FleetConsumptionService $fuelService, FleetTollService $tollService): JsonResponse
    {
        $fuelCount = $fuelService->syncFromExternalSource($vehicle);
        $tollCount = $tollService->syncFromExternalSource($vehicle);

        return response()->json([
            'message' => 'Synchronisation terminée.',
            'imported_consumptions' => $fuelCount,
            'imported_tolls' => $tollCount,
            'current_odometer' => $vehicle->refresh()->current_odometer,
        ]);
    }
}
