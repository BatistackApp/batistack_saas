<?php

namespace App\Http\Controllers\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Exceptions\Expense\ExpenseModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ExpenseReportRequest;
use App\Models\Expense\ExpenseReport;
use App\Services\Expense\ExpenseWorkflowService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseReportController extends Controller
{
    public function __construct(
        protected ExpenseWorkflowService $workflowService
    ) {}

    /**
     * Liste des notes de frais de l'utilisateur connecté.
     */
    public function index(Request $request): JsonResponse
    {
        $query = ExpenseReport::query()->withCount('items');

        // Filtrage par utilisateur si non-admin
        if (!auth()->user()->hasRole('tenant_admin')) {
            $query->where('user_id', auth()->id());
        }

        // Filtre par statut optionnel
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest()->paginate($request->per_page ?? 15));
    }

    /**
     * Création d'un nouveau brouillon.
     */
    public function store(ExpenseReportRequest $request): JsonResponse
    {
        $report = ExpenseReport::create([
            'label'      => $request->label,
            'user_id'    => $request->user_id ?? auth()->id(),
            'tenants_id' => auth()->user()->tenants_id,
            'status'     => ExpenseStatus::Draft,
        ]);

        return response()->json([
            'message' => 'Brouillon de note de frais créé.',
            'data'    => $report
        ], 201);
    }

    /**
     * Détail d'une note avec ses lignes.
     */
    public function show(ExpenseReport $report): JsonResponse
    {
        return response()->json($report->load(['items.category', 'items.project', 'user']));
    }

    /**
     * Passage du statut Brouillon à Soumis.
     */
    public function submit(ExpenseReport $report): JsonResponse
    {
        try {
            $this->workflowService->submit($report);
            return response()->json(['message' => 'Note de frais soumise avec succès.']);
        } catch (ExpenseModuleException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function update(ExpenseReportRequest $request, ExpenseReport $report): JsonResponse
    {
        $report->update($request->validated());

        return response()->json($report, 200);
    }

    public function destroy(ExpenseReport $expenseReport): JsonResponse
    {
        if (!in_array($expenseReport->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            return response()->json(['error' => 'Impossible de supprimer une note déjà soumise ou payée.'], 422);
        }

        $expenseReport->delete();
        return response()->json(['message' => 'Note de frais supprimée.']);
    }
}
