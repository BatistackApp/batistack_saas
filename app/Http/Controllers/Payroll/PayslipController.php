<?php

namespace App\Http\Controllers\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayslipAdjustmentRequest;
use App\Models\Payroll\Payslip;
use App\Models\Payroll\PayslipLine;
use App\Services\Payroll\PayrollCalculationService;
use Illuminate\Http\JsonResponse;

class PayslipController extends Controller
{
    public function __construct(protected PayrollCalculationService $calculationService) {}

    /**
     * Liste des bulletins d'une période.
     */
    public function index(int $periodId): JsonResponse
    {
        $payslips = Payslip::where('payroll_period_id', $periodId)
            ->with('employee:id,first_name,last_name,job_title')
            ->get();

        return response()->json($payslips);
    }

    /**
     * Vue détaillée d'un bulletin (pour l'édition ou PDF).
     */
    public function show(Payslip $payslip): JsonResponse
    {
        return response()->json(
            $payslip->load(['lines', 'employee', 'period'])
        );
    }

    /**
     * Ajout d'une prime ou retenue manuelle.
     */
    public function addAdjustment(PayslipAdjustmentRequest $request, Payslip $payslip): JsonResponse
    {
        $data = $request->validated();

        $line = $payslip->lines()->create([
            'label' => $data['label'],
            'amount_gain' => $data['type'] === 'earning' ? $data['amount'] : 0,
            'amount_deduction' => $data['type'] === 'deduction' ? abs($data['amount']) : 0,
            'type' => $data['type'],
            'is_manual_adjustment' => true,
            'sort_order' => 90,
        ]);

        // Recalcul des totaux du bulletin (Net à payer, etc.)
        $this->calculationService->refreshTotals($payslip);

        return response()->json($line->load('payslip'), 201);
    }

    /**
     * Suppression d'un ajustement manuel.
     */
    public function removeAdjustment(Payslip $payslip, PayslipLine $payslipLine): JsonResponse
    {
        if ($payslip->status !== PayrollStatus::Draft) {
            return response()->json(['error' => 'Bulletin verrouillé.'], 422);
        }

        if (!$payslipLine->is_manual_adjustment) {
            return response()->json(['error' => 'Impossible de supprimer une ligne calculée automatiquement.'], 422);
        }

        $payslipLine->delete();
        $this->calculationService->refreshTotals($payslip);

        return response()->json(['message' => 'Ligne supprimée, totaux mis à jour.']);
    }
}
