<?php

namespace App\Http\Requests\Expense;

use App\Models\Projects\ProjectPhase;
use Illuminate\Foundation\Http\FormRequest;

class StoreExpenseItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'expense_report_id' => ['required', 'exists:expense_reports,id'],
            'expense_category_id' => [
                'required',
                'exists:expense_categories,id,tenants_id,' . auth()->user()->tenants_id
            ],

            // --- Imputation Analytique (BTP) ---
            'project_id' => ['nullable', 'exists:projects,id,tenants_id,' . auth()->user()->tenants_id],
            'project_phase_id' => [
                'nullable',
                'exists:project_phases,id',
                // Règle de cohérence : La phase doit appartenir au chantier
                function ($attribute, $value, $fail) {
                    if ($this->project_id && $value) {
                        $exists = ProjectPhase::where('id', $value)
                            ->where('project_id', $this->project_id)
                            ->exists();
                        if (!$exists) {
                            $fail("Le lot/phase sélectionné n'est pas rattaché au chantier choisi.");
                        }
                    }
                },
            ],

            'date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:500'],

            // --- Flags Métier ---
            'is_mileage' => ['boolean'],
            'is_billable' => ['boolean'], // Refacturation client

            // --- Validation conditionnelle IK (Kilométrage) ---
            'distance_km' => ['required_if:is_mileage,true', 'nullable', 'numeric', 'min:0.1'],
            'vehicle_power' => ['required_if:is_mileage,true', 'nullable', 'integer', 'min:1'],
            'start_location' => ['nullable', 'string', 'max:255'],
            'end_location' => ['nullable', 'string', 'max:255'],

            // --- Validation financière standard ---
            // Le montant TTC est obligatoire sauf si c'est de l'IK (car calculé par le service)
            'amount_ttc' => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'min:0'],
            'tax_rate' => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'in:0,2.1,5.5,10,20'],

            'receipt_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'], // 10Mo max pour les factures
        ];
    }

    public function messages(): array
    {
        return [
            'distance_km.required_if' => 'La distance est obligatoire pour les frais kilométriques.',
            'vehicle_power.required_if' => 'La puissance fiscale est requise pour appliquer le barème légal.',
            'amount_ttc.required_unless' => 'Le montant TTC est obligatoire pour les frais standards.',
            'project_phase_id.exists' => 'La phase de projet sélectionnée est invalide.',
        ];
    }

    public function authorize(): bool
    {
        $reportId = $this->input('expense_report_id') ?? $this->route('expense_report')?->id;

        return auth()->user()->reports()
            ->whereIn('status', [\App\Enums\Expense\ExpenseStatus::Draft, \App\Enums\Expense\ExpenseStatus::Rejected])
            ->where('id', $reportId)
            ->exists() || auth()->user()->can('tenant.expenses.manage');
    }
}
