<?php

namespace App\Http\Requests\GPAO;

use Illuminate\Foundation\Http\FormRequest;

class StoreWorkOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ouvrage_id' => ['required', 'exists:ouvrages,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'], // Dépôt pour le produit fini
            'project_id' => ['nullable', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'quantity_planned' => ['required', 'numeric', 'min:0.001'],
            'priority' => ['integer', 'min:1', 'max:5'],
            'planned_start_at' => ['required', 'date', 'after_or_equal:today'],
            'planned_end_at' => ['required', 'date', 'after:planned_start_at'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('gpao.manage');
    }
}
