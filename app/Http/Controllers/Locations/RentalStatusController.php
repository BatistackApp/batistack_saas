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

            if ($newStatus === RentalStatus::ACTIVE) {
                $this->workflowService->startRental($rentalContract, $actualDate);
                $message = 'Location activée : le matériel est désormais sur site.';
            } elseif ($newStatus === RentalStatus::ENDED) {
                $this->workflowService->endRental($rentalContract, $actualDate);
                $message = 'Location terminée : les derniers coûts ont été imputés.';
            } else {
                $rentalContract->update(['status' => $newStatus]);
                $message = 'Statut mis à jour.';
            }

            return response()->json(['message' => $message]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
