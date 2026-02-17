<?php

namespace App\Http\Requests\Commerce;

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoicesRequest extends FormRequest
{
    public function rules(): array
    {
        $invoiceId = $this->route('invoice')?->id;

        return [
            'tenants_id' => ['required', 'exists:tenants,id'],
            'tiers_id' => ['required', 'exists:tiers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'quote_id' => ['nullable', 'exists:quotes,id'],

            'type' => ['required', Rule::enum(InvoiceType::class)],
            'reference' => [
                'required',
                'string',
                'max:50',
                // Unique par tenant pour respecter la migration
                Rule::unique('invoices')->where('tenants_id', $this->tenants_id)->ignore($invoiceId),
            ],
            'situation_number' => ['nullable', 'integer', 'min:1', Rule::requiredIf($this->type === \App\Enums\Commerce\InvoiceType::Progress)],

            // Calculs financiers (souvent pilotés par le serveur mais validables)
            'total_ht' => ['required', 'numeric', 'min:0'],
            'total_tva' => ['required', 'numeric', 'min:0'],
            'total_ttc' => ['required', 'numeric', 'min:0'],

            // Spécificités BTP
            'retenue_garantie_pct' => ['nullable', 'numeric', 'between:0,100'],
            'retenue_garantie_amount' => ['nullable', 'numeric', 'min:0'],
            'compte_prorata_amount' => ['nullable', 'numeric', 'min:0'],
            'is_autoliquidation' => ['boolean'],

            'status' => ['required', Rule::enum(InvoiceStatus::class)],
            'due_date' => ['required', 'date'],

            // Validation des lignes (items)
            'items' => ['required', 'array', 'min:1'],
            'items.*.label' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'not_in:0'],
            'items.*.unit_price_ht' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
            'items.*.progress_percentage' => ['nullable', 'numeric', 'between:0,100'],
            'items.*.quote_item_id' => ['nullable', 'exists:quote_items,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'reference.unique' => 'Cette référence de facture est déjà utilisée pour votre entreprise.',
            'items.required' => 'Une facture doit contenir au moins une ligne.',
            'situation_number.required_if' => 'Le numéro de situation est requis pour une facture de type situation.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
