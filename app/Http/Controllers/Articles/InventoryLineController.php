<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\InventoryLineRequest;
use App\Models\Articles\InventoryLine;

class InventoryLineController extends Controller
{
    public function index()
    {
        return InventoryLine::all();
    }

    public function store(InventoryLineRequest $request)
    {
        return InventoryLine::create($request->validated());
    }

    public function show(InventoryLine $inventoryLine)
    {
        return $inventoryLine;
    }

    public function update(InventoryLineRequest $request, InventoryLine $inventoryLine)
    {
        $inventoryLine->update($request->validated());

        return $inventoryLine;
    }

    public function destroy(InventoryLine $inventoryLine)
    {
        $inventoryLine->delete();

        return response()->json();
    }
}
