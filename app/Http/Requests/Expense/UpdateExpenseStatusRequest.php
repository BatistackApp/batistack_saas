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
            'reason' => [
                'required_if:status,'.ExpenseStatus::Rejected->value,
                'nullable',
                'string',
                'max:1000',
            ],
            // Optionnel : date de paiement si statut Paid
            'paid_at' => ['required_if:status,'.ExpenseStatus::Paid->value, 'nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Le nouveau statut est obligatoire.',
            'reason.required_if' => 'Un motif de refus doit être fourni pour rejeter une note de frais.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('tenant.expenses.manage');
    }
}
