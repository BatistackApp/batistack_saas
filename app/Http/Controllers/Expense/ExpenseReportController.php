<?php

namespace App\Http\Controllers\Expense;

use App\Exceptions\Expense\ExpenseModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\ExpenseReportRequest;
use App\Models\Expense\ExpenseReport;
use App\Services\Expense\ExpenseWorkflowService;
use Illuminate\Http\JsonResponse;

class ExpenseReportController extends Controller
{
    public function __construct(
        protected ExpenseWorkflowService $workflowService
    ) {}

    /**
     * Liste des notes de frais de l'utilisateur connecté.
     */
    public function index(): JsonResponse
    {
        $reports = ExpenseReport::where('user_id', auth()->id())
            ->withCount('items')
            ->latest()
            ->paginate();

        return response()->json($reports);
    }

    /**
     * Création d'un nouveau brouillon.
     */
    public function store(ExpenseReportRequest $request): JsonResponse
    {
        $report = ExpenseReport::create(array_merge(
            $request->validated(),
            [
                'user_id' => auth()->id(),
                'tenants_id' => auth()->user()->tenants_id,
                'status' => 'draft',
            ]
        ));

        return response()->json($report, 201);
    }

    /**
     * Détail d'une note avec ses lignes.
     */
    public function show(ExpenseReport $report): JsonResponse
    {
        return response()->json($report->load(['items.category', 'items.chantier']));
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
}
