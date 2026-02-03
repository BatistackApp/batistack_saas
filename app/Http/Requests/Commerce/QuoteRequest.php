<?php

namespace App\Http\Requests\Commerce;

use App\Enums\Commerce\QuoteStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class QuoteRequest extends FormRequest
{
    public function rules(): array
    {
        $quoteId = $this->route('quote')?->id;
        return [
            'tenants_id' => ['required', 'exists:tenants,id'],
            'customer_id' => ['required', 'exists:tiers,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'reference' => [
                'required',
                'string',
                'max:50',
                Rule::unique('quotes')->where('tenants_id', $this->tenants_id)->ignore($quoteId),
            ],
            'status' => ['required', Rule::enum(QuoteStatus::class)],
            'valid_until' => ['nullable', 'date', 'after:today'],

            // Totaux
            'total_ht' => ['required', 'numeric', 'min:0'],
            'total_tva' => ['required', 'numeric', 'min:0'],
            'total_ttc' => ['required', 'numeric', 'min:0'],

            // Items du devis (Ouvrages ou articles)
            'items' => ['required', 'array', 'min:1'],
            'items.*.ouvrage_id' => ['nullable', 'exists:ouvrages,id'],
            'items.*.article_id' => ['nullable', 'exists:articles,id'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price_ht' => ['required', 'numeric', 'min:0'],
            'items.*.order' => ['integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'valid_until.after' => 'La date de validité du devis doit être dans le futur.',
            'items.min' => 'Un devis doit contenir au moins un ouvrage ou un article.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
