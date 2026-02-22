<?php

namespace App\Http\Requests\Intervention;

use App\Enums\Intervention\BillingType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInterventionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:tiers,id'],
            // Optionnel ici pour permettre à l'Observer d'assigner le "Dépôt Mobile" du créateur
            'warehouse_id' => ['nullable', 'exists:warehouses,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'planned_at' => ['required', 'date', 'after_or_equal:today'],
            'billing_type' => ['required', Rule::enum(BillingType::class)],

            // Possibilité d'assigner des techniciens dès la création
            'technician_ids' => ['nullable', 'array'],
            'technician_ids.*' => ['exists:employees,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'planned_at.after_or_equal' => "La date de planification ne peut pas être dans le passé.",
            'customer_id.exists' => "Le client sélectionné est invalide.",
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('intervention.manage');
    }
}
