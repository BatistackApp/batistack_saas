<?php

namespace App\Http\Requests\Banque;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'bank_transaction_id' => ['nullable', 'exists:bank_transactions'],
            'invoices_id' => ['required', 'exists:invoices'],
            'amount' => ['required', 'numeric'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required'],
            'reference' => ['nullable'],
            'created_by' => ['required', 'exists:users'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
