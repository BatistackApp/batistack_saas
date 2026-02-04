<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class OuvrageConsumptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'ouvrage_id' => ['required', 'exists:ouvrages,id'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'project_id' => ['required', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'reference' => ['nullable', 'string', 'max:100'],
            'wastage_factor_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required' => 'L\'imputation à un projet est obligatoire pour consommer un ouvrage.',
            'quantity.gt' => 'La quantité réalisée doit être positive.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
