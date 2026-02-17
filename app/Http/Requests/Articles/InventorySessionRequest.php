<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class InventorySessionRequest extends FormRequest
{
    public function rules(): array
    {
        if ($this->isMethod('post') && ! $this->route('inventory_session')) {
            return [
                'warehouse_id' => [
                    'required',
                    'exists:warehouses,id',
                ],
                'notes' => ['nullable', 'string', 'max:1000'],
            ];
        }

        // Si nous sommes sur l'enregistrement d'un comptage (InventoryLine)
        if ($this->has('article_id')) {
            return [
                'article_id' => ['required', 'exists:articles,id'],
                'counted_quantity' => ['required', 'numeric', 'min:0'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'Le dépôt est obligatoire pour ouvrir un inventaire.',
            'warehouse_id.exists' => 'Le dépôt sélectionné est invalide.',
            'article_id.required' => 'L\'article est requis pour le comptage.',
            'counted_quantity.required' => 'La quantité comptée est obligatoire.',
            'counted_quantity.numeric' => 'La quantité doit être un nombre valide.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
