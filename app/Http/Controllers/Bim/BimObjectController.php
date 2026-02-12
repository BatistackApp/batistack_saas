<?php

namespace App\Http\Controllers\Bim;

use App\Http\Controllers\Controller;
use App\Models\Bim\BimModel;
use App\Services\Bim\BimMappingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BimObjectController extends Controller
{
    public function __construct(protected BimMappingService $mappingService) {}

    /**
     * Récupère le contexte métier d'un objet cliqué dans le viewer 3D.
     */
    public function getContext(Request $request, BimModel $bimModel, string $guid): JsonResponse
    {
        $context = $this->mappingService->getBusinessContext($guid, $bimModel->id);

        if (! $context['found']) {
            return response()->json(['error' => 'Objet non indexé.'], 404);
        }

        return response()->json($context);
    }

    /**
     * Liste filtrable des objets pour recherche textuelle (ex: "Béton").
     */
    public function search(Request $request, BimModel $bimModel): JsonResponse
    {
        $query = $bimModel->objects();

        if ($request->has('type')) {
            $query->where('ifc_type', $request->type);
        }

        if ($request->has('search')) {
            $query->where('label', 'LIKE', "%{$request->search}%");
        }

        return response()->json($query->limit(50)->get());
    }
}
