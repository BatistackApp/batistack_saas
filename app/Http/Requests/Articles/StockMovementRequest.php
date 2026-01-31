<?php

namespace App\Http\Requests\Articles;

use App\Enums\Articles\StockMovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'article_id' => ['required', 'exists:articles,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => ['required', 'numeric', 'gt:0'], // Toujours positif, le sens est donné par le type

            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],

            'project_id' => [
                Rule::requiredIf(fn () => $this->type === StockMovementType::Exit->value),
                'nullable',
                'exists:projects,id'
            ],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],

            'target_warehouse_id' => [
                Rule::requiredIf(fn () => $this->type === StockMovementType::Transfer->value),
                'nullable',
                'exists:warehouses,id',
                'different:warehouse_id' // Empêcher le transfert vers le même dépôt
            ],

            'unit_cost_ht' => [
                Rule::requiredIf(fn () => $this->type === StockMovementType::Entry->value),
                'nullable',
                'numeric',
                'min:0'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.required_if' => 'Un projet doit être sélectionné pour une sortie de stock chantier.',
            'target_warehouse_id.required_if' => 'Un dépôt de destination est requis pour un transfert.',
            'target_warehouse_id.different' => 'Le dépôt de destination doit être différent du dépôt d\'origine.',
            'unit_cost_ht.required_if' => 'Le prix d\'achat unitaire est requis pour valoriser l\'entrée en stock.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
