<?php

namespace App\Http\Controllers\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Exceptions\Expense\ExpenseModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\UpdateExpenseStatusRequest;
use App\Models\Expense\ExpenseReport;
use App\Services\Expense\ExpenseWorkflowService;
use Illuminate\Http\JsonResponse;

class ExpenseApprovalController extends Controller
{
    public function __construct(
        protected ExpenseWorkflowService $workflowService
    ) {}

    /**
     * Liste des notes en attente de validation
     */
    public function pending(): JsonResponse
    {
        $reports = ExpenseReport::where('status', ExpenseStatus::Submitted)
            ->with(['user', 'items'])
            ->latest()
            ->paginate();

        return response()->json($reports);
    }

    /**
     * Approbation ou Refus d'une note.
     */
    public function updateStatus(UpdateExpenseStatusRequest $request, ExpenseReport $expenseReport): JsonResponse
    {
        try {
            if ($request->status === ExpenseStatus::Approved->value) {
                $this->workflowService->approve($expenseReport, auth()->id());

                return response()->json(['message' => 'Note approuvée et imputation lancée.']);
            }

            if ($request->status === ExpenseStatus::Rejected->value) {
                // On met à jour le motif avant de rejeter
                $expenseReport->update(['rejection_reason' => $request->reason]);
                $this->workflowService->reject($expenseReport, $request->reason);

                return response()->json(['message' => 'Note rejetée. L\'employé a été notifié.']);
            }

            return response()->json(['error' => 'Action non supportée.'], 400);

        } catch (ExpenseModuleException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
