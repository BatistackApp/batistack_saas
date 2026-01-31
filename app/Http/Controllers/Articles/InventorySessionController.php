<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\InventorySessionRequest;
use App\Models\Articles\InventorySession;

class InventorySessionController extends Controller
{
    public function index()
    {
        return InventorySession::all();
    }

    public function store(InventorySessionRequest $request)
    {
        return InventorySession::create($request->validated());
    }

    public function show(InventorySession $inventorySession)
    {
        return $inventorySession;
    }

    public function update(InventorySessionRequest $request, InventorySession $inventorySession)
    {
        $inventorySession->update($request->validated());

        return $inventorySession;
    }

    public function destroy(InventorySession $inventorySession)
    {
        $inventorySession->delete();

        return response()->json();
    }
}
