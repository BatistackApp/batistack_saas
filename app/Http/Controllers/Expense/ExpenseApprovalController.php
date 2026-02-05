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
     * Approbation ou Refus d'une note.
     */
    public function updateStatus(UpdateExpenseStatusRequest $request, ExpenseReport $report): JsonResponse
    {
        try {
            if ($request->status === ExpenseStatus::Approved) {
                $this->workflowService->approve($report, auth()->id());
                $msg = 'Note de frais approuvée et coûts imputés aux chantiers.';
            } else {
                $this->workflowService->reject($report);
                $msg = 'Note de frais rejetée.';
            }

            return response()->json(['message' => $msg]);
        } catch (ExpenseModuleException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }
}
