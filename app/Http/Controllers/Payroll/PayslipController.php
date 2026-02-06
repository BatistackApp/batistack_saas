<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayslipAdjustmentRequest;
use App\Models\Payroll\Payslip;
use Illuminate\Http\JsonResponse;

class PayslipController extends Controller
{
    /**
     * Détails d'un bulletin avec ses lignes.
     */
    public function show(Payslip $payslip): JsonResponse
    {
        return response()->json($payslip->load(['lines', 'employee', 'period']));
    }

    /**
     * Ajout manuel d'une ligne d'ajustement (Prime, retenue exceptionnelle).
     */
    public function addAdjustment(PayslipAdjustmentRequest $request, Payslip $payslip): JsonResponse
    {
        $data = $request->validated();

        $line = $payslip->lines()->create([
            'label' => $data['label'],
            'amount_gain' => $data['type'] === 'earning' ? $data['amount'] : 0,
            'amount_deduction' => $data['type'] === 'deduction' ? abs($data['amount']) : 0,
            'type' => $data['type'],
            'sort_order' => 50, // Positionnement personnalisé
        ]);

        // On force le recalcul du net de l'en-tête
        $payslip->update([
            'net_to_pay' => $payslip->lines()->sum('amount_gain') - $payslip->lines()->sum('amount_deduction')
        ]);

        return response()->json($line, 201);
    }
}
