<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class InventorySessionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'warehouse_id' => ['required', 'exists:warehouses'],
            'reference' => ['required'],
            'status' => ['required'],
            'opened_at' => ['required', 'date'],
            'closed_at' => ['nullable', 'date'],
            'validated_at' => ['nullable', 'date'],
            'created_by' => ['required', 'exists:users'],
            'validated_by' => ['nullable', 'exists:users'],
            'notes' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
