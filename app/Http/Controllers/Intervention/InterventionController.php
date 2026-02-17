<?php

namespace App\Http\Controllers\Intervention;

use App\Enums\Intervention\InterventionStatus;
use App\Exceptions\Intervention\InterventionModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intervention\StoreInterventionRequest;
use App\Http\Requests\Intervention\UpdateInterventionRequest;
use App\Models\Intervention\Intervention;
use App\Services\Intervention\InterventionWorkflowService;
use Illuminate\Http\JsonResponse;

class InterventionController extends Controller
{
    public function __construct(
        protected InterventionWorkflowService $workflowService
    ) {}

    public function index(): JsonResponse
    {
        $interventions = Intervention::with(['customer', 'project'])
            ->latest()
            ->paginate();

        return response()->json($interventions);
    }

    public function store(StoreInterventionRequest $request): JsonResponse
    {
        $intervention = Intervention::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        return response()->json($intervention, 201);
    }

    public function show(Intervention $intervention): JsonResponse
    {
        return response()->json($intervention->load(['items.article', 'items.ouvrage', 'technicians', 'customer']));
    }

    public function update(UpdateInterventionRequest $request, Intervention $intervention)
    {
        $intervention->update($request->validated());

        return response()->json($intervention);
    }

    /**
     * @throws InterventionModuleException
     */
    public function destroy(Intervention $intervention)
    {
        if ($intervention->status !== InterventionStatus::Planned) {
            throw new InterventionModuleException(
                message: 'Impossible de supprimer une intervention en cours.',
                code: 422
            );
        }

        $intervention->delete();

        return response()->json(['message' => 'Intervention supprimée.']);
    }

    /**
     * Déclenche le passage au statut "En cours".
     */
    public function start(Intervention $intervention): JsonResponse
    {
        try {
            $this->workflowService->start($intervention);

            return response()->json(['message' => 'Intervention démarrée.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Déclenche la clôture (Déstockage + Heures + Marge).
     */
    public function complete(Intervention $intervention): JsonResponse
    {
        try {
            $this->workflowService->complete($intervention);

            return response()->json(['message' => 'Intervention clôturée avec succès.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
