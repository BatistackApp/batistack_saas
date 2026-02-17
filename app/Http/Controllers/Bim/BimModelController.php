<?php

namespace App\Http\Controllers\Bim;

use App\Http\Controllers\Controller;
use App\Http\Requests\Bim\StoreBimModelRequest;
use App\Http\Requests\Bim\UpdateBimModelRequest;
use App\Models\Bim\BimModel;
use App\Services\Bim\BimModelService;
use Illuminate\Http\JsonResponse;

class BimModelController extends Controller
{
    public function __construct(protected BimModelService $modelService) {}

    public function index(): JsonResponse
    {
        $models = BimModel::with('project')->latest()->paginate();

        return response()->json($models);
    }

    public function store(StoreBimModelRequest $request): JsonResponse
    {
        // Upload sur S3 via le service (non détaillé ici mais implicite dans le workflow)
        $path = $request->file('ifc_file')->store('tenants/'.auth()->user()->tenants_id.'/bim/models', 'public');

        $model = BimModel::create(array_merge(
            $request->validated(),
            [
                'tenants_id' => auth()->user()->tenants_id,
                'file_path' => $path,
                'file_size' => $request->file('ifc_file')->getSize(),
            ]
        ));

        return response()->json($model, 201);
    }

    public function show(BimModel $bimModel): JsonResponse
    {
        // On renvoie l'URL signée S3 pour que Three.js puisse charger le fichier
        $viewerUrl = $this->modelService->getViewerUrl($bimModel);

        return response()->json([
            'model' => $bimModel->load('project'),
            'viewer_url' => $viewerUrl,
        ]);
    }

    public function update(UpdateBimModelRequest $request, BimModel $bimModel): JsonResponse
    {
        $bimModel->update($request->validated());

        return response()->json($bimModel);
    }

    public function destroy(BimModel $bimModel): JsonResponse
    {
        // Suppression du fichier S3 et des métadonnées DB
        \Storage::disk('public')->delete($bimModel->file_path);
        $bimModel->delete();

        return response()->json(null, 204);
    }
}
