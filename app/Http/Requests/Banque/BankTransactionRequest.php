<?php

namespace App\Http\Requests\Banque;

use App\Enums\Banque\BankTransactionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BankTransactionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_account_id' => ['required', 'exists:bank_accounts,id'],
            'value_date' => ['required', 'date', 'before_or_equal:today'],
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', Rule::enum(BankTransactionType::class)],
            'raw_metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'value_date.before_or_equal' => 'La date de l\'opération ne peut pas être dans le futur.',
            'amount.not_in' => 'Le montant d\'une transaction ne peut pas être nul.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
