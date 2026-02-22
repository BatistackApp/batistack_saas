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
            'expense_category_id' => ['required', 'exists:expense_categories,id'],

            // Imputation Analytique (Recommandation 1)
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_phase_id' => [
                'nullable',
                'exists:project_phases,id',
                // Règle de cohérence : la phase doit appartenir au projet
                function ($attribute, $value, $fail) {
                    if ($this->project_id && $value) {
                        $exists = ProjectPhase::where('id', $value)
                            ->where('project_id', $this->project_id)
                            ->exists();
                        if (! $exists) {
                            $fail("La phase sélectionnée n'appartient pas au projet choisi.");
                        }
                    }
                },
            ],

            'date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:500'],

            // Flags métier (Recommandation 3)
            'is_mileage' => ['boolean'],
            'is_billable' => ['boolean'], // Pour refacturation client

            // Gestion des IK (Recommandation 2)
            'distance_km' => ['required_if:is_mileage,true', 'nullable', 'numeric', 'min:0.1'],
            'vehicle_power' => ['required_if:is_mileage,true', 'nullable', 'integer', 'min:1'],
            'start_location' => ['nullable', 'string', 'max:255'],
            'end_location' => ['nullable', 'string', 'max:255'],

            // Montants financiers (Non requis pour l'IK car calculés)
            'amount_ttc' => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'min:0'],
            'tax_rate' => ['required_unless:is_mileage,true', 'nullable', 'numeric', 'in:0,2.1,5.5,10,20'],

            'receipt_path' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
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
