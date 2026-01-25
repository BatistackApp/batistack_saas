<?php

namespace App\Services\Payroll;

use App\Models\Payroll\PayrollSlip;

class PayrollValidationService
{
    /**
     * @return array<string, mixed>
     */
    public function validate(PayrollSlip $slip): array
    {
        $errors = [];

        // Vérifier les montants
        if ($slip->gross_amount <= 0) {
            $errors['gross_amount'] = 'Le montant brut doit être supérieur à 0';
        }

        if ($slip->total_hours_work <= 0) {
            $errors['total_hours_work'] = 'Aucune heure travaillée détectée';
        }

        // Vérifier les lignes
        if ($slip->lines()->count() === 0) {
            $errors['lines'] = 'La fiche doit avoir au moins une ligne';
        }

        // Vérifier l\'employé
        if (! $slip->employee) {
            $errors['employee'] = 'Employé introuvable';
        }

        // Vérifier la cohérence
        $linesTotal = $slip->lines()->sum('amount');
        if (abs($linesTotal - $slip->gross_amount) > 0.01) {
            $errors['consistency'] = 'Incohérence entre les lignes et le montant brut';
        }

        return [
            'valid' => count($errors) === 0,
            'errors' => $errors,
        ];
    }
}
