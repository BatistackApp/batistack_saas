<?php

namespace App\Http\Controllers\Expense;

use App\Enums\Expense\ExpenseStatus;
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
            $status = ExpenseStatus::from($request->status);

            switch ($status) {
                case ExpenseStatus::Approved:
                    $this->workflowService->approve($expenseReport, auth()->id());
                    $msg = 'Note de frais approuvée.';
                    break;

                case ExpenseStatus::Rejected:
                    $this->workflowService->reject($expenseReport, $request->reason);
                    $msg = 'Note de frais rejetée.';
                    break;

                case ExpenseStatus::Paid:
                    // Logique optionnelle de paiement direct
                    $expenseReport->update(['status' => ExpenseStatus::Paid]);
                    $msg = 'Note marquée comme payée.';
                    break;

                default:
                    return response()->json(['error' => 'Action non gérée.'], 400);
            }

            return response()->json(['message' => $msg]);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
