<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\StockMovementRequest;
use App\Models\Articles\StockMovement;

class StockMovementController extends Controller
{
    public function index()
    {
        return StockMovement::all();
    }

    public function store(StockMovementRequest $request)
    {
        return StockMovement::create($request->validated());
    }

    public function show(StockMovement $stockMovement)
    {
        return $stockMovement;
    }

    public function update(StockMovementRequest $request, StockMovement $stockMovement)
    {
        $stockMovement->update($request->validated());

        return $stockMovement;
    }

    public function destroy(StockMovement $stockMovement)
    {
        $stockMovement->delete();

        return response()->json();
    }
}
