<?php

namespace App\Http\Controllers\Locations;

use App\Enums\Locations\RentalStatus;
use App\Exceptions\Locations\RentalModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreRentalContractRequest;
use App\Http\Requests\Locations\UpdateRentalContractRequest;
use App\Models\Locations\RentalContract;
use Illuminate\Http\JsonResponse;

class RentalContractController extends Controller
{
    public function index(): JsonResponse
    {
        $contracts = RentalContract::with(['provider', 'project', 'phase'])
            ->latest()
            ->paginate();

        return response()->json($contracts);
    }

    public function store(StoreRentalContractRequest $request): JsonResponse
    {
        $contract = RentalContract::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        return response()->json($contract, 201);
    }

    public function show(RentalContract $rentalContract): JsonResponse
    {
        return response()->json($rentalContract->load(['items', 'inspections.inspector', 'provider']));
    }

    public function update(UpdateRentalContractRequest $request, RentalContract $rentalContract): JsonResponse
    {
        $rentalContract->update($request->validated());
        return response()->json($rentalContract);
    }

    /**
     * @throws RentalModuleException
     */
    public function destroy(RentalContract $rentalContract)
    {
        if ($rentalContract->status !== RentalStatus::DRAFT) {
            throw new RentalModuleException(
                message: 'Impossible de supprimer un contrat de location en cours.',
                code: 422
            );
        }

        $rentalContract->delete();
        return response()->json(['message' => 'Contrat de location supprimé.']);
    }
}
