<?php

namespace App\Http\Controllers\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\AbsenceRequestReviewRequest;
use App\Http\Requests\HR\AbsenceRequestStoreRequest;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\Employee;
use App\Services\HR\AbsenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsenceRequestController extends Controller
{
    public function __construct(
        protected AbsenceService $absenceService
    ) {}

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
        $employee = Employee::findOrFail($data['employee_id']);

        // Gestion de l'upload si présent
        if ($request->hasFile('justification_file')) {
            $path = $request->file('justification_file')->store('hr/absences', 'public');
            $data['justification_path'] = $path;
        }

        $absence = $this->absenceService->createRequest($employee, $data);

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
        $data = $request->validated();
        $data['validated_by'] = auth()->id();
        $data['validated_at'] = now();

        $absenceRequest->update($data);

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
        if ($absenceRequest->status !== AbsenceRequestStatus::Pending) {
            return response()->json(['error' => 'Impossible de supprimer une demande déjà traitée'], 422);
        }

        $absenceRequest->delete();

        return response()->json(['message' => 'Demande supprimée']);
    }
}
