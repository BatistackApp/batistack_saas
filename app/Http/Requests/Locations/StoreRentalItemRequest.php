<?php

namespace App\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalItemRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'article_id' => ['nullable', 'exists:articles,id'],
            'label' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'daily_rate_ht' => ['required', 'numeric', 'min:0'],
            'weekly_rate_ht' => ['required', 'numeric', 'min:0'],
            'monthly_rate_ht' => ['required', 'numeric', 'min:0'],
            'is_weekend_included' => ['required', 'boolean'],
            'insurance_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function authorize(): bool
    {
        $contract = $this->route('rental_contract');

        // On ne peut ajouter du matériel que si le contrat n'est pas terminé
        return $contract && in_array($contract->status, [
            \App\Enums\Locations\RentalStatus::DRAFT,
            \App\Enums\Locations\RentalStatus::ACTIVE,
        ]);
    }
}
