<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\WarehouseRequest;
use App\Models\Articles\Warehouse;

class WarehouseController extends Controller
{
    public function index()
    {
        return Warehouse::all();
    }

    public function store(WarehouseRequest $request)
    {
        return Warehouse::create($request->validated());
    }

    public function show(Warehouse $warehouse)
    {
        return $warehouse;
    }

    public function update(WarehouseRequest $request, Warehouse $warehouse)
    {
        $warehouse->update($request->validated());

        return $warehouse;
    }

    public function destroy(Warehouse $warehouse)
    {
        $warehouse->delete();

        return response()->json();
    }
}
