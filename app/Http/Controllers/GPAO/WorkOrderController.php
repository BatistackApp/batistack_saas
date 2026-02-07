<?php

namespace App\Http\Controllers\GPAO;

use App\Http\Controllers\Controller;
use App\Http\Requests\GPAO\FinalizeWorkOrderRequest;
use App\Http\Requests\GPAO\StoreWorkOrderRequest;
use App\Http\Requests\GPAO\UpdateWorkOrderRequest;
use App\Models\GPAO\WorkOrder;
use App\Services\GPAO\ProductionOrchestrator;
use App\Services\GPAO\ProductionValuationService;
use Illuminate\Http\JsonResponse;

class WorkOrderController extends Controller
{
    public function __construct(
        protected ProductionOrchestrator $orchestrator,
        protected ProductionValuationService $valuationService
    ) {}

    public function index(): JsonResponse
    {
        $workOrders = WorkOrder::with(['ouvrage', 'warehouse', 'project'])
            ->latest()
            ->paginate();

        return response()->json($workOrders);
    }

    public function store(StoreWorkOrderRequest $request): JsonResponse
    {
        $workOrder = WorkOrder::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        // AUTOMATISATION : Explosion immédiate de la nomenclature (BOM)
        $this->orchestrator->initializeFromOuvrage($workOrder);

        return response()->json($workOrder->load('components'), 201);
    }

    public function show(WorkOrder $workOrder): JsonResponse
    {
        return response()->json($workOrder->load(['components', 'operations.workCenter']));
    }

    public function update(UpdateWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        $workOrder->update($request->validated());

        return response()->json($workOrder);
    }

    /**
     * Clôture finale de l'OF avec valorisation du produit fini.
     */
    public function finalize(FinalizeWorkOrderRequest $request, WorkOrder $workOrder): JsonResponse
    {
        try {
            $this->valuationService->finalizeWorkOrder(
                $workOrder,
                $request->input('quantity_produced') // Passage de la quantité réelle
            );
            return response()->json(['message' => 'Ordre de fabrication clôturé et produit fini entré en stock.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
