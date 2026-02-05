<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleInspectionRequest;
use App\Models\Fleet\VehicleInspection;
use Illuminate\Http\JsonResponse;

class VehicleInspectionController extends Controller
{
    /**
     * Enregistre un nouveau passage en contrôle.
     */
    public function store(VehicleInspectionRequest $request): JsonResponse
    {
        $inspection = VehicleInspection::create($request->validated());

        // Si un fichier de rapport est joint, on pourrait gérer l'upload ici via un service GED
        if ($request->hasFile('report_file')) {
            // $path = $request->file('report_file')->store('fleet/inspections');
            // $inspection->update(['report_path' => $path]);
        }

        return response()->json($inspection, 201);
    }
}
