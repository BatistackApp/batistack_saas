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
                $request->validated(),
                ['tenants_id' => auth()->user()->tenants_id]
            ));

            // Si des techniciens sont fournis à la création
            if ($request->has('technician_ids')) {
                $intervention->technicians()->sync($request->technician_ids);
            }

            return $intervention;
        });

        return response()->json($intervention, 201);
    }

    public function show(Intervention $intervention): JsonResponse
    {
        return response()->json($intervention->load(['items.article', 'items.ouvrage', 'technicians', 'customer', 'warehouse']));
    }

    public function update(UpdateInterventionRequest $request, Intervention $intervention)
    {
        $intervention->update($request->validated());

        return response()->json($intervention);
    }

    /**
     * Changement de statut (Annulation, etc.)
     */
    public function updateStatus(UpdateInterventionStatusRequest $request, Intervention $intervention): JsonResponse
    {
        $intervention->update($request->validated());

        return response()->json([
            'message' => 'Statut mis à jour : '.$intervention->status->getLabel(),
            'intervention' => $intervention,
        ]);
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
                // 1. Mise à jour des données du rapport (colonnes réelles)
                $intervention->update($request->safe()->only([
                    'report_notes',
                    'client_signature',
                    'completed_at',
                ]));

                // 2. Mise à jour des heures des techniciens (Table pivot)
                foreach ($request->technicians as $techData) {
                    $intervention->technicians()->updateExistingPivot(
                        $techData['employee_id'],
                        ['hours_spent' => $techData['hours_spent']]
                    );
                }

                // 3. Appel du workflow de clôture (sans passer le tableau pollué par 'technicians')
                $this->workflowService->complete($intervention, $request->except(['technicians']));
            });

            return response()->json(['message' => 'Intervention clôturée et rapport généré.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
