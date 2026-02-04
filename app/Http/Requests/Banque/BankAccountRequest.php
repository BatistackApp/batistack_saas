<?php

namespace App\Http\Requests\Banque;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'name' => ['required'],
            'bank_name' => ['nullable'],
            'bank_iban' => ['nullable'],
            'type' => ['required'],
            'bridge_id' => ['nullable'],
            'bridge_item_id' => ['nullable'],
            'sync_status' => ['required'],
            'last_synced_at' => ['nullable', 'date'],
            'initial_balance' => ['required', 'numeric'],
            'current_balance' => ['required', 'numeric'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
