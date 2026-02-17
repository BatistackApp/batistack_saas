<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\PurchaseOrderReceiveRequest;
use App\Http\Requests\Commerce\PurchaseOrderRequest;
use App\Models\Articles\Warehouse;
use App\Models\Commerce\PurchaseOrder;
use App\Services\Commerce\PurchaseOrderService;
use Illuminate\Http\JsonResponse;

class PurchaseOrderController extends Controller
{
    public function __construct(protected PurchaseOrderService $purchaseService) {}

    public function index(): JsonResponse
    {
        $orders = PurchaseOrder::with(['supplier', 'project', 'createdBy'])
            ->latest()
            ->paginate(15);

        return response()->json($orders);
    }

    /**
     * Création d'un bon de commande avec ses lignes.
     */
    public function store(PurchaseOrderRequest $request): JsonResponse
    {
        $order = PurchaseOrder::create($request->validated());

        if ($request->has('items')) {
            $order->items()->createMany($request->items);
        }

        return response()->json($order, 201);
    }

    /**
     * RÉCEPTION DE MARCHANDISE
     * Fait le pont entre la commande et le stock réel.
     */
    public function receive(PurchaseOrderReceiveRequest $request, PurchaseOrder $order): JsonResponse
    {
        $request->validated();

        try {
            $warehouse = Warehouse::findOrFail($request->warehouse_id);
            $this->purchaseService->recordReception($order, $warehouse, $request->items);

            return response()->json(['message' => 'Réception enregistrée et stocks mis à jour.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(PurchaseOrder $order): JsonResponse
    {
        return response()->json($order->load(['items.article', 'supplier', 'project']));
    }
}
