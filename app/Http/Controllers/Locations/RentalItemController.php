<?php

namespace App\Http\Controllers\Locations;

use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreRentalItemRequest;
use App\Models\Locations\RentalContract;
use App\Models\Locations\RentalItem;
use Illuminate\Http\JsonResponse;

class RentalItemController extends Controller
{
    public function store(StoreRentalItemRequest $request, RentalContract $rentalContract): JsonResponse
    {
        $item = $rentalContract->items()->create($request->validated());
        return response()->json($item, 201);
    }

    public function destroy(RentalContract $rentalContract, RentalItem $rentalItem): JsonResponse
    {
        if ($rentalContract->status === \App\Enums\Locations\RentalStatus::INVOICED) {
            return response()->json(['error' => 'Contrat verrouillé.'], 403);
        }

        $rentalItem->delete();
        return response()->json(null, 204);
    }
}
