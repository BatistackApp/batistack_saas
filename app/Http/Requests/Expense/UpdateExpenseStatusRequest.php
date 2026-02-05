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
            'reason' => ['required_if:status,' . ExpenseStatus::Rejected->value, 'string', 'max:1000'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('validate-expenses');
    }
}
