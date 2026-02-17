<?php

namespace App\Http\Controllers\Bim;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bim\StoreBimViewRequest;
use App\Models\Bim\BimModel;
use App\Models\Bim\BimView;
use Illuminate\Http\JsonResponse;

class BimViewController extends Controller
{
    public function index(BimModel $bimModel): JsonResponse
    {
        return response()->json($bimModel->views()->with('user')->get());
    }

    public function store(StoreBimViewRequest $request): JsonResponse
    {
        $view = BimView::create(array_merge(
            $request->validated(),
            ['user_id' => auth()->id()]
        ));

        return response()->json($view, 201);
    }

    public function destroy(BimView $bimView): JsonResponse
    {
        $bimView->delete();

        return response()->json(null, 204);
    }
}
