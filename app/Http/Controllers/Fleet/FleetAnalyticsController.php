<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Vehicle;
use App\Services\Fleet\FleetAnalyticsService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetAnalyticsController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $analyticsService
    ) {}

    /**
     * Retourne le TCO détaillé d'un véhicule sur une période.
     */
    public function getVehicleTco(Request $request, Vehicle $vehicle): JsonResponse
    {
        $startDate = $request->has('start_date') ? CarbonImmutable::parse($request->start_date) : now()->subYear();
        $endDate = $request->has('end_date') ? CarbonImmutable::parse($request->end_date) : now();

        $tcoData = $this->analyticsService->getVehicleTco($vehicle, $startDate, $endDate);

        // Ajout de la consommation moyenne pour compléter le rapport
        $tcoData['average_consumption'] = $this->analyticsService->calculateAverageConsumption($vehicle);

        return response()->json([
            'vehicle' => [
                'id' => $vehicle->id,
                'name' => $vehicle->name,
                'license_plate' => $vehicle->license_plate,
                'internal_code' => $vehicle->internal_code,
            ],
            'period' => [
                'from' => $startDate->toDateString(),
                'to' => $endDate->toDateString(),
            ],
            'analytics' => $tcoData
        ]);
    }

    /**
     * Rapport consolidé de la flotte pour le tableau de bord de pilotage.
     */
    public function getFleetGlobalStats(): JsonResponse
    {
        $vehicles = Vehicle::where('is_active', true)->get();

        $stats = $vehicles->map(function ($vehicle) {
            return [
                'vehicle' => $vehicle->internal_code,
                'odometer' => $vehicle->current_odometer,
                'consumption' => $this->analyticsService->calculateAverageConsumption($vehicle),
                'tco_year' => $this->analyticsService->getVehicleTco($vehicle, CarbonImmutable::now()->subYear())['total_tco_ht']
            ];
        });

        return response()->json([
            'total_vehicles' => $vehicles->count(),
            'fleet_details' => $stats,
            'alerts_count' => \App\Models\Fleet\VehicleInspection::where('next_due_date', '<', now()->addDays(15))->count()
        ]);
    }
}
