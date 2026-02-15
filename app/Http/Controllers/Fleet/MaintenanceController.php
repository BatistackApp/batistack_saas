<?php

namespace App\Http\Controllers\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\MaintenanceCompletionRequest;
use App\Http\Requests\Fleet\MaintenanceRequest;
use App\Models\Fleet\VehicleMaintenance;
use App\Services\Fleet\FleetMaintenanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MaintenanceController extends Controller
{
    public function __construct(protected FleetMaintenanceService $maintenanceService) {}

    /**
     * Liste filtrée des interventions de maintenance.
     */
    public function index(Request $request): JsonResponse
    {
        $maintenances = VehicleMaintenance::with(['vehicle', 'plan', 'reporter'])
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->status, fn($q) => $q->where('maintenance_status', $request->status))
            ->latest()
            ->paginate(20);

        return response()->json($maintenances);
    }

    /**
     * Signalement ou planification d'une maintenance.
     */
    public function store(MaintenanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Génération d'une référence unique MTN-YYYY-XXXX
        $data['reported_by'] = auth()->id();
        $data['reported_at'] = now();
        $data['maintenance_status'] = MaintenanceStatus::Reported;

        $maintenance = VehicleMaintenance::create($data);

        return response()->json($maintenance, 201);
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
        $maintenance->update([
            'maintenance_status' => MaintenanceStatus::InProgress,
            'started_at' => now(),
        ]);

        return response()->json($maintenance);
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
            'data'    => $maintenance->fresh()
        ]);
    }

    /**
     * Annulation d'une intervention.
     */
    public function cancel(VehicleMaintenance $maintenance): JsonResponse
    {
        $maintenance->update(['maintenance_status' => MaintenanceStatus::Cancelled]);
        return response()->json(['message' => 'Maintenance annulée.']);
    }
}
