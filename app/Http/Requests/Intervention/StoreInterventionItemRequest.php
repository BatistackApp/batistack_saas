<?php

namespace App\Http\Requests\Intervention;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterventionItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'article_id' => ['required_without:ouvrage_id', 'nullable', 'exists:articles,id'],
            'ouvrage_id' => ['required_without:article_id', 'nullable', 'exists:ouvrages,id'],
            'article_serial_number_id' => ['nullable', 'exists:article_serial_numbers,id'],
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.001'],

            // Prix de vente (peut être forcé manuellement si autorisé)
            'unit_price_ht' => ['required', 'numeric', 'min:0'],

            // Type de ligne
            'is_billable' => ['required', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        $intervention = $this->route('intervention');

        // Verrouillage si l'intervention est clôturée
        return $intervention && $intervention->status === \App\Enums\Intervention\InterventionStatus::InProgress;
    }
}
