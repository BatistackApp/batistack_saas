<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'supplier_id' => ['required', 'exists:tiers'],
            'project_id' => ['nullable', 'exists:projects'],
            'reference' => ['required'],
            'status' => ['required'],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date'],
            'total_ht' => ['required', 'numeric'],
            'total_tva' => ['required', 'numeric'],
            'notes' => ['nullable'],
            'created_by' => ['required', 'exists:users'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
