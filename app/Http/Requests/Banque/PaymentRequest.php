<?php

namespace App\Http\Requests\Banque;

use App\Enums\Banque\BankPaymentMethod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bank_transaction_id' => ['nullable', 'exists:bank_transactions,id'],
            'invoice_id' => ['required', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'method' => ['required', Rule::enum(BankPaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'La facture associée est obligatoire pour valider le règlement.',
            'amount.min' => 'Le montant du règlement doit être supérieur à zéro.',
            'method.Illuminate\Validation\Rules\Enum' => 'Le mode de paiement est invalide.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
