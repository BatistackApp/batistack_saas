<?php

namespace App\Http\Controllers\Locations;

use App\Enums\Locations\RentalStatus;
use App\Exceptions\Locations\RentalModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\StoreRentalContractRequest;
use App\Http\Requests\Locations\UpdateRentalContractRequest;
use App\Models\Locations\RentalContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RentalContractController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contracts = RentalContract::query()
            ->with(['provider:id,name', 'project:id,name', 'phase:id,name'])
            ->withCount('items')
            // Filtres rapides
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->project_id, fn($q) => $q->where('project_id', $request->project_id))
            ->when($request->provider_id, fn($q) => $q->where('provider_id', $request->provider_id))
            ->latest()
            ->paginate($request->per_page ?? 15);

        return response()->json($contracts);
    }

    public function store(StoreRentalContractRequest $request): JsonResponse
    {
        $contract = RentalContract::create($request->validated());

        return response()->json([
            'message' => 'Contrat de location créé avec succès.',
            'data' => $contract
        ], 201);
    }

    public function show(RentalContract $rentalContract): JsonResponse
    {
        return response()->json($rentalContract->load([
            'items',
            'inspections.inspector:id,first_name,last_name',
            'provider',
            'project'
        ]));
    }

    public function update(UpdateRentalContractRequest $request, RentalContract $rentalContract): JsonResponse
    {
        $rentalContract->update($request->validated());

        return response()->json([
            'message' => 'Contrat mis à jour.',
            'data' => $rentalContract
        ]);
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
