<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Http\Requests\HR\AbsenceRequestReviewRequest;
use App\Http\Requests\HR\AbsenceRequestStoreRequest;
use App\Models\HR\AbsenceRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceRequestController extends Controller
{
    /**
     * Liste des demandes (Filtres pour manager ou employé)
     */
    public function index(Request $request): JsonResponse
    {
        $query = AbsenceRequest::with(['employee', 'manager']);

        // Si l'utilisateur n'est pas admin, il ne voit que ses demandes
        if (! auth()->user()->can('payroll.manage')) {
            $query->whereHas('employee', function ($q) {
                $q->where('user_id', auth()->id());
            });
        }

        return response()->json($query->latest()->paginate());
    }

    /**
     * Soumission d'une nouvelle demande par l'employé
     */
    public function store(AbsenceRequestStoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        if ($request->hasFile('justification_file')) {
            $path = $request->file('justification_file')->store('tenant/'.auth()->user()->tenants_id.'/hr/absences', 'public');
            $data['justification_path'] = $path;
        }

        $absence = AbsenceRequest::create($data);

        return response()->json([
            'message' => 'Demande d\'absence enregistrée',
            'data' => $absence,
        ], 201);
    }

    /**
     * Approbation ou Refus par le manager
     */
    public function review(AbsenceRequestReviewRequest $request, AbsenceRequest $absenceRequest): JsonResponse
    {
        $absenceRequest->update($request->validated());

        return response()->json([
            'message' => 'Décision enregistrée avec succès',
            'data' => $absenceRequest,
        ]);
    }

    /**
     * Suppression d'une demande (Géré par l'Observer pour le fichier)
     */
    public function destroy(AbsenceRequest $absenceRequest): JsonResponse
    {
        // On ne peut supprimer que si la demande est encore en attente
        if ($absenceRequest->status->value !== 'pending') {
            return response()->json(['error' => 'Impossible de supprimer une demande déjà traitée'], 422);
        }

        $absenceRequest->delete();

        return response()->json(['message' => 'Demande supprimée']);
    }
}
