<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class QuoteRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'customer_id' => ['required', 'exists:tiers'],
            'project_id' => ['nullable', 'exists:projects'],
            'reference' => ['required'],
            'status' => ['required'],
            'total_ht' => ['required', 'numeric'],
            'total_tva' => ['required', 'numeric'],
            'total_ttc' => ['required', 'numeric'],
            'valid_until' => ['nullable', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
