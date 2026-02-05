<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleConsumptionRequest;
use App\Models\Fleet\Vehicle;
use App\Services\Fleet\FleetConsumptionService;
use Illuminate\Http\JsonResponse;

class VehicleConsumptionController extends Controller
{
    /**
     * Saisie manuelle d'un plein ou d'une recharge.
     */
    public function store(VehicleConsumptionRequest $request, FleetConsumptionService $service): JsonResponse
    {
        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        $consumption = $service->recordFuelConsumption($vehicle, $request->validated());

        return response()->json($consumption, 201);
    }
}
