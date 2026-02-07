<?php

namespace App\Http\Controllers\GPAO;

use App\Enums\GPAO\OperationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\GPAO\UpdateOperationStatusRequest;
use App\Models\GPAO\WorkOrderOperation;
use App\Services\GPAO\WorkOrderExecutionService;
use Illuminate\Http\JsonResponse;

class WorkOrderOperationController extends Controller
{
    public function __construct(
        protected WorkOrderExecutionService $executionService
    ) {}

    /**
     * Mise à jour du statut d'une opération et enregistrement du temps.
     */
    public function updateStatus(UpdateOperationStatusRequest $request, WorkOrderOperation $operation): JsonResponse
    {
        try {
            $newStatus = OperationStatus::from($request->status);

            if ($newStatus === OperationStatus::Running) {
                $this->executionService->startOperation($operation);
            } elseif ($newStatus === OperationStatus::Finished) {
                $this->executionService->completeOperation(
                    $operation,
                    (float) $request->time_actual_minutes
                );
            } else {
                $operation->update(['status' => $newStatus]);
            }

            return response()->json(['message' => 'Opération mise à jour.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
