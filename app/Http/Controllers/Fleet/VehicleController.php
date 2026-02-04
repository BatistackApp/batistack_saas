<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleRequest;
use App\Models\Fleet\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        return Vehicle::all();
    }

    public function store(VehicleRequest $request)
    {
        return Vehicle::create($request->validated());
    }

    public function show(Vehicle $vehicle)
    {
        return $vehicle;
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $vehicle->update($request->validated());

        return $vehicle;
    }

    public function destroy(Vehicle $vehicle)
    {
        $vehicle->delete();

        return response()->json();
    }
}
