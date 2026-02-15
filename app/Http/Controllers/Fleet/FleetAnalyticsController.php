<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Models\Fleet\Vehicle;
use App\Services\Fleet\FleetAnalyticsService;
use App\Services\Fleet\FleetComplianceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FleetAnalyticsController extends Controller
{
    public function __construct(
        protected FleetAnalyticsService $analyticsService,
        protected FleetComplianceService $complianceService,
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
            'vehicle' => $vehicle->only(['id', 'name', 'license_plate', 'internal_code']),
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
        $vehicles = Vehicle::where('is_active', true)
            ->with(['currentAssignment.user', 'inspections'])
            ->get();

        $complianceReport = [
            'total_compliant' => 0,
            'total_critical' => 0,
            'critical_vehicles' => []
        ];

        $stats = $vehicles->map(function ($vehicle) use (&$complianceReport) {
            // Calcul TCO rapide
            $tco = $this->analyticsService->getVehicleTco($vehicle, CarbonImmutable::now()->subYear());

            // Audit de conformité
            $isCompliant = true;
            $driverIssue = null;

            if ($vehicle->currentAssignment?->user) {
                $check = $this->complianceService->checkDriverCompliance($vehicle, $vehicle->currentAssignment->user);
                if (!$check['status']) {
                    $isCompliant = false;
                    $driverIssue = $check['message'];
                }
            }

            if (!$isCompliant) {
                $complianceReport['total_critical']++;
                $complianceReport['critical_vehicles'][] = [
                    'id' => $vehicle->id,
                    'internal_code' => $vehicle->internal_code,
                    'issue' => $driverIssue
                ];
            } else {
                $complianceReport['total_compliant']++;
            }

            return [
                'vehicle' => $vehicle->internal_code,
                'odometer' => $vehicle->current_odometer,
                'consumption' => $this->analyticsService->calculateAverageConsumption($vehicle),
                'tco_year_ht' => $tco['total_tco_ht'],
                'status' => $isCompliant ? 'ok' : 'alert'
            ];
        });

        return response()->json([
            'summary' => [
                'total_vehicles' => $vehicles->count(),
                'compliance' => $complianceReport,
            ],
            'fleet_details' => $stats
        ]);
    }
}
