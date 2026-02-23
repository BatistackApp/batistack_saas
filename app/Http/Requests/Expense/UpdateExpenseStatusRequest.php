<?php

namespace App\Http\Requests\Expense;

use App\Enums\Expense\ExpenseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateExpenseStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(ExpenseStatus::class)],

            // Le motif est obligatoire en cas de rejet pour que le salarié puisse corriger
            'reason' => [
                'required_if:status,' . ExpenseStatus::Rejected->value,
                'nullable',
                'string',
                'min:5',
                'max:1000'
            ],

            // On peut demander une date de paiement si le statut passe à "Paid"
            'paid_at' => [
                'required_if:status,' . ExpenseStatus::Paid->value,
                'nullable',
                'date'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le nouveau statut est obligatoire.',
            'reason.required_if' => 'Un motif de refus doit être fourni pour rejeter une note de frais.',
            'paid_at.required_if' => 'La date de paiement est obligatoire pour marquer la note comme payée.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('tenant.expenses.manage');
    }
}
