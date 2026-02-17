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
                'required_if:status,' . ExpenseStatus::Rejected->value,
                'nullable',
                'string',
                'max:1000'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required_if' => 'Un motif est obligatoire pour justifier le rejet de la note de frais.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->hasPermissionTo('tenant.expenses.validate');
    }
}
