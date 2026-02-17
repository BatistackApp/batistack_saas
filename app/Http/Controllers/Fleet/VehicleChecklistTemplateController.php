<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleChecklistTemplateRequest;
use App\Models\Fleet\VehicleChecklistTemplate;
use Illuminate\Http\JsonResponse;

class VehicleChecklistTemplateController extends Controller
{
    /**
     * Liste des templates disponibles par tenant.
     */
    public function index(): JsonResponse
    {
        $templates = VehicleChecklistTemplate::withCount('questions')->get();

        return response()->json($templates);
    }

    /**
     * Création d'un nouveau questionnaire avec ses questions.
     */
    public function store(VehicleChecklistTemplateRequest $request): JsonResponse
    {
        $template = VehicleChecklistTemplate::create($request->safe()->except('questions'));

        if ($request->has('questions')) {
            $template->questions()->createMany($request->input('questions'));
        }

        return response()->json($template->load('questions'), 201);
    }

    /**
     * Détails d'un questionnaire spécifique.
     */
    public function show(VehicleChecklistTemplate $template): JsonResponse
    {
        return response()->json($template->load('questions'));
    }

    /**
     * Mise à jour du template.
     */
    public function update(VehicleChecklistTemplateRequest $request, VehicleChecklistTemplate $template): JsonResponse
    {
        $template->update($request->safe()->except('questions'));

        return response()->json($template);
    }

    /**
     * Archive un template (Soft Delete via le trait si configuré, ou simple flag).
     */
    public function destroy(VehicleChecklistTemplate $template): JsonResponse
    {
        $template->delete();

        return response()->json(['message' => 'Template supprimé.']);
    }
}
