<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class GeneratePayslipRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // Permet de générer pour toute l'entreprise ou une sélection
            'employee_ids' => ['nullable', 'array'],
            'employee_ids.*' => ['exists:employees,id'],
            'department' => ['nullable', 'string'],

            // Option pour écraser les brouillons existants
            'overwrite_existing' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'employee_ids.*.exists' => 'Un ou plusieurs salariés sélectionnés sont invalides.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('payroll.manage');
    }
}
