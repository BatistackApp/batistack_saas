<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleAssignmentRequest;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class VehicleAssignmentController extends Controller
{
    /**
     * Affecte un véhicule à un chantier ou à un collaborateur.
     */
    public function store(VehicleAssignmentRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            $vehicle = Vehicle::findOrFail($request->vehicle_id);

            // 1. Clôture de l'affectation précédente si elle existe
            $vehicle->assignments()->whereNull('ended_at')->update(['ended_at' => now()]);

            // 2. Création de la nouvelle affectation
            $assignment = VehicleAssignment::create($request->validated());

            return response()->json($assignment, 201);
        });
    }

    /**
     * Libère un véhicule (fin d'affectation).
     */
    public function release(VehicleAssignment $assignment): JsonResponse
    {
        $assignment->update(['ended_at' => now()]);
        return response()->json(['message' => 'Véhicule libéré.']);
    }
}
