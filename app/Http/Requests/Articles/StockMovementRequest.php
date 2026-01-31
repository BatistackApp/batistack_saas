<?php

namespace App\Http\Requests\Articles;

use App\Enums\Articles\StockMovementType;
use App\Enums\Articles\TrackingType;
use App\Models\Articles\Article;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StockMovementRequest extends FormRequest
{
    public function rules(): array
    {
        $article = $this->article_id ? Article::find($this->article_id) : null;
        $isSerialized = $article && $article->tracking_type === TrackingType::SerialNumber;

        return [
            'tenants_id' => ['required', 'exists:tenants,id'],
            'article_id' => ['required', 'exists:articles,id'],
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'type' => ['required', Rule::enum(StockMovementType::class)],
            'quantity' => [
                'required',
                'numeric',
                // Correction : On accepte 1 ou -1 pour les articles sérialisés
                $isSerialized ? Rule::in([1, -1]) : '',
                Rule::when($this->type !== StockMovementType::Adjustment->value, ['gt:0']),
                Rule::when($this->type === StockMovementType::Adjustment->value, ['not_in:0']),
            ],
            'serial_number' => [
                Rule::requiredIf(fn () => $isSerialized && $this->type === StockMovementType::Entry->value),
                'nullable',
                'string',
                'max:255'
            ],
            'serial_number_id' => [
                Rule::requiredIf(fn () => $isSerialized && $this->type !== StockMovementType::Entry->value),
                'nullable',
                'exists:article_serial_numbers,id'
            ],
            'adjustment_type' => [
                Rule::requiredIf(fn () => $this->type === StockMovementType::Adjustment->value),
                'nullable',
                'in:gain,loss'
            ],

            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],

            'project_id' => [
                Rule::requiredIf(fn () => in_array($this->type, [StockMovementType::Exit->value, StockMovementType::Return->value])),
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
            'project_id.required_if' => 'Un projet doit être sélectionné pour ce type de mouvement chantier (Sortie ou Retour).',
            'serial_number.required_if' => 'Le numéro de série est obligatoire pour enregistrer cet équipement.',
            'serial_number_id.required_if' => 'Veuillez sélectionner le numéro de série spécifique pour ce mouvement.',
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
