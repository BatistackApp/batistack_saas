<?php

namespace App\Http\Controllers\Locations;

use App\Enums\Locations\RentalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Locations\UpdateRentalStatusRequest;
use App\Models\Locations\RentalContract;
use App\Services\Locations\RentalWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;

class RentalStatusController extends Controller
{
    public function __construct(
        protected RentalWorkflowService $workflowService
    ) {}

    /**
     * Change le statut du contrat (Activation ou Fin).
     */
    public function updateStatus(UpdateRentalStatusRequest $request, RentalContract $rentalContract): JsonResponse
    {
        try {
            $newStatus = RentalStatus::from($request->status);
            $actualDate = Carbon::parse($request->actual_date);

            match ($newStatus) {
                // Étape 1 : Le matériel arrive sur chantier
                RentalStatus::ACTIVE => $this->workflowService->startRental($rentalContract, $actualDate),

                // Étape 2 : On appelle le loueur pour dire qu'on a fini (Fin de facturation)
                RentalStatus::OFF_HIRE => $this->workflowService->requestOffHire(
                    $rentalContract,
                    $actualDate,
                    $request->off_hire_reference
                ),

                // Étape 3 : Le loueur a physiquement récupéré le matériel
                RentalStatus::ENDED => $this->workflowService->confirmReturn($rentalContract, $actualDate),

                // Autres cas simples (Annulation, etc.)
                default => $rentalContract->update(['status' => $newStatus]),
            };

            return response()->json([
                'message' => 'Le contrat est passé au statut : '.$newStatus->value,
                'data' => $rentalContract->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
