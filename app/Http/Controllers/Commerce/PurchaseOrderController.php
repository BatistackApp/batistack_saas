<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\PurchaseOrderRequest;
use App\Models\Commerce\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return PurchaseOrder::all();
    }

    public function store(PurchaseOrderRequest $request)
    {
        return PurchaseOrder::create($request->validated());
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return $purchaseOrder;
    }

    public function update(PurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->validated());

        return $purchaseOrder;
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        return response()->json();
    }
}
