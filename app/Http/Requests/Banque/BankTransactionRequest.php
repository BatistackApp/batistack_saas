<?php

namespace App\Http\Requests\Banque;

use Illuminate\Foundation\Http\FormRequest;

class BankTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'bank_account_id' => ['required', 'exists:bank_accounts'],
            'value_date' => ['required', 'date'],
            'label' => ['required'],
            'amount' => ['required', 'numeric'],
            'type' => ['required'],
            'external_id' => ['nullable'],
            'import_hash' => ['nullable'],
            'is_reconciled' => ['boolean'],
            'raw_metadata' => ['nullable'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
