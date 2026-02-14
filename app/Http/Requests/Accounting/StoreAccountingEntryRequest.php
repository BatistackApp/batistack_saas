<?php

namespace App\Http\Requests\Accounting;

use Illuminate\Foundation\Http\FormRequest;

class StoreAccountingEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'journal_id' => ['required', 'exists:journals,id'],
            'accounting_date' => ['required', 'date'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // Validation des lignes (Min 2 pour le double sens)
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.chart_of_account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.description' => ['nullable', 'string', 'max:255'],
            'lines.*.debit' => ['required_without:lines.*.credit', 'numeric', 'min:0'],
            'lines.*.credit' => ['required_without:lines.*.debit', 'numeric', 'min:0'],

            // Imputation Analytique BTP
            'lines.*.project_id' => ['nullable', 'exists:projects,id'],
            'lines.*.project_phase_id' => ['nullable', 'exists:project_phases,id'],
        ];
    }

    /**
     * Hook de validation pour l'équilibre financier (BCMath).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $lines = $this->input('lines', []);
            $totalDebit = '0';
            $totalCredit = '0';

            foreach ($lines as $line) {
                $totalDebit = bcadd($totalDebit, (string) ($line['debit'] ?? 0), 4);
                $totalCredit = bcadd($totalCredit, (string) ($line['credit'] ?? 0), 4);
            }

            if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
                $validator->errors()->add(
                    'lines',
                    "Déséquilibre détecté : Total Débit ({$totalDebit}) ≠ Total Crédit ({$totalCredit})."
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'lines.min' => 'Une écriture comptable doit comporter au moins deux lignes.',
            'lines.*.chart_of_account_id.exists' => 'Le compte comptable sélectionné est invalide.',
            'accounting_date.date' => 'La date d\'écriture doit être un format de date valide.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('accounting.manage');
    }
}
