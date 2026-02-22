<?php

namespace App\Http\Controllers\Intervention;

use App\Enums\Intervention\InterventionStatus;
use App\Exceptions\Intervention\InterventionModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Intervention\CompleteInterventionRequest;
use App\Http\Requests\Intervention\StoreInterventionRequest;
use App\Http\Requests\Intervention\UpdateInterventionRequest;
use App\Http\Requests\Intervention\UpdateInterventionStatusRequest;
use App\Models\Intervention\Intervention;
use App\Services\Intervention\InterventionWorkflowService;
use DB;
use Illuminate\Http\JsonResponse;

class InterventionController extends Controller
{
    public function __construct(
        protected InterventionWorkflowService $workflowService
    ) {}

    public function index(): JsonResponse
    {
        $interventions = Intervention::with(['customer', 'project', 'technicians'])
            ->latest()
            ->paginate();

        return response()->json($interventions);
    }

    public function store(StoreInterventionRequest $request): JsonResponse
    {
        $intervention = DB::transaction(function () use ($request) {
            $intervention = Intervention::create(array_merge(
                $request->safe()->except('technician_ids'),
                ['tenants_id' => auth()->user()->tenants_id]
            ));

            if ($request->has('technician_ids')) {
                $intervention->technicians()->sync($request->technician_ids);
            }

            return $intervention;
        });

        return response()->json($intervention->load('customer'), 201);
    }

    public function show(Intervention $intervention): JsonResponse
    {
        return response()->json($intervention->load([
            'items.article',
            'items.ouvrage',
            'technicians',
            'customer',
            'project',
        ]));
    }

    public function update(UpdateInterventionRequest $request, Intervention $intervention)
    {
        $intervention->update($request->validated());

        return response()->json($intervention);
    }

    /**
     * Changement de statut (Annulation, etc.)
     */
    /**
     * Changement manuel de statut (Annulation, Report, etc.).
     */
    public function updateStatus(UpdateInterventionStatusRequest $request, Intervention $intervention): JsonResponse
    {
        $intervention->update([
            'status' => $request->status,
            'description' => $request->reason
                ? $intervention->description."\nNote de statut : ".$request->reason
                : $intervention->description,
        ]);

        return response()->json(['message' => 'Statut mis à jour avec succès.', 'status' => $intervention->status]);
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
    public function complete(CompleteInterventionRequest $request, Intervention $intervention): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $intervention) {
                // 1. Enregistrement des données qualitatives du rapport
                $intervention->update($request->safe()->only([
                    'report_notes',
                    'client_signature',
                    'completed_at',
                ]));

                // 2. Mise à jour des heures finales saisies sur mobile
                foreach ($request->technicians as $techData) {
                    $intervention->technicians()->updateExistingPivot(
                        $techData['employee_id'],
                        ['hours_spent' => $techData['hours_spent']]
                    );
                }

                // 3. Exécution du workflow métier (Appel au service)
                $this->workflowService->complete($intervention, $request->except('technicians'));
            });

            return response()->json(['message' => 'Intervention clôturée. Rapport et flux générés.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
