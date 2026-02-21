<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreRentalInspectionRequest;
use App\Models\Locations\RentalContract;
use Illuminate\Http\JsonResponse;

class RentalInspectionController extends Controller
{
    /**
     * Enregistre un état des lieux avec photos et signatures.
     */
    public function store(StoreRentalInspectionRequest $request, RentalContract $rentalContract): JsonResponse
    {
        // Enregistrement de l'inspection
        $inspection = $rentalContract->inspections()->create([
            'inspector_id' => auth()->id(),
            'type' => $request->type,
            'notes' => $request->notes,
            'photos' => $request->photos, // Stockés via un cast JSON ou MediaLibrary
            'client_signature' => $request->client_signature,
            'provider_signature' => $request->provider_signature,
        ]);

        return response()->json([
            'message' => 'État des lieux enregistré.',
            'data' => $inspection->load('inspector:id,first_name,last_name'),
        ], 201);
    }
}
