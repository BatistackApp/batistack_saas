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
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'planned_at' => ['required', 'date', 'after_or_equal:today'],
            'billing_type' => ['required', Rule::enum(BillingType::class)],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('intervention.manage');
    }
}
