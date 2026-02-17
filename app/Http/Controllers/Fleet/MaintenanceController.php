<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\MaintenanceCompletionRequest;
use App\Http\Requests\Fleet\MaintenanceRequest;
use App\Models\Fleet\VehicleMaintenance;
use App\Services\Fleet\FleetMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    public function __construct(protected FleetMaintenanceService $maintenanceService) {}

    /**
     * Liste filtrée des interventions de maintenance.
     */
    public function index(Request $request): JsonResponse
    {
        $maintenances = VehicleMaintenance::with(['vehicle', 'plan', 'reporter'])
            ->when($request->vehicle_id, fn ($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->status, fn ($q) => $q->where('maintenance_status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($maintenances);
    }

    /**
     * Signalement ou planification d'une maintenance.
     */
    public function store(MaintenanceRequest $request): JsonResponse
    {
        $maintenance = $this->maintenanceService->reportMaintenance(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'message' => 'Demande de maintenance enregistrée.',
            'data' => $maintenance,
        ], 201);
    }

    /**
     * Détails d'une intervention.
     */
    public function show(VehicleMaintenance $maintenance): JsonResponse
    {
        return response()->json($maintenance->load(['vehicle', 'plan', 'reporter']));
    }

    /**
     * Passage de l'intervention à l'état "En cours" (début des travaux).
     */
    public function start(VehicleMaintenance $maintenance): JsonResponse
    {
        $this->maintenanceService->startMaintenance($maintenance);

        return response()->json([
            'message' => 'La maintenance est désormais en cours.',
            'data' => $maintenance->fresh(),
        ]);
    }

    /**
     * Clôture technique et financière de la maintenance.
     * Déclenche la mise à jour des compteurs du véhicule via l'Observer.
     */
    public function complete(MaintenanceCompletionRequest $request, VehicleMaintenance $maintenance): JsonResponse
    {
        $this->maintenanceService->completeIntervention($maintenance, $request->validated());

        return response()->json([
            'message' => 'La maintenance a été clôturée avec succès.',
            'data' => $maintenance->fresh(),
        ]);
    }

    /**
     * Annulation d'une intervention.
     */
    public function cancel(VehicleMaintenance $maintenance): JsonResponse
    {
        $this->maintenanceService->cancelMaintenance($maintenance);

        return response()->json([
            'message' => 'La maintenance a été annulée.',
            'data' => $maintenance->fresh(),
        ]);
    }
}
