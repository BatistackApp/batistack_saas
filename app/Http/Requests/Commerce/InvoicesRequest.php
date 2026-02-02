<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class InvoicesRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'tiers_id' => ['required', 'exists:tiers'],
            'project_id' => ['required', 'exists:projects'],
            'quote_id' => ['nullable', 'exists:quotes'],
            'type' => ['required'],
            'reference' => ['required'],
            'situation_number' => ['nullable'],
            'total_ht' => ['required', 'numeric'],
            'total_tva' => ['required', 'numeric'],
            'total_ttc' => ['required', 'numeric'],
            'retenue_garantie_pct' => ['required', 'numeric'],
            'retenue_garantie_amount' => ['required', 'numeric'],
            'compte_prorata_amount' => ['required', 'numeric'],
            'is_autoliquidation' => ['boolean'],
            'status' => ['required'],
            'due_date' => ['required', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
