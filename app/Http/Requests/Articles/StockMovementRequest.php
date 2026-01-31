<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class StockMovementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'article_id' => ['required', 'exists:articles'],
            'warehouse_id' => ['required', 'exists:warehouses'],
            'project_id' => ['nullable', 'exists:projects'],
            'project_phase_id' => ['nullable', 'exists:project_phases'],
            'target_warehouse_id' => ['nullable', 'exists:warehouses'],
            'type' => ['required'],
            'quantity' => ['required', 'numeric'],
            'unit_cost_ht' => ['nullable', 'numeric'],
            'reference' => ['nullable'],
            'notes' => ['nullable'],
            'user_id' => ['required', 'exists:users'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
