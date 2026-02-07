<?php

namespace App\Http\Controllers\GPAO;

use App\Http\Controllers\Controller;
use App\Http\Requests\GPAO\StoreWorkCenterRequest;
use App\Models\GPAO\WorkCenter;
use Illuminate\Http\JsonResponse;

class WorkCenterController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WorkCenter::where('is_active', true)->get());
    }

    public function store(StoreWorkCenterRequest $request): JsonResponse
    {
        $workCenter = WorkCenter::create(array_merge(
            $request->validated(),
            ['tenant_id' => auth()->user()->tenants_id]
        ));

        return response()->json($workCenter, 201);
    }
}
