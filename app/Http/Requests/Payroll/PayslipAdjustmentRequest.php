<?php

namespace App\Http\Requests\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Enums\Payroll\PayslipLineType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayslipAdjustmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', Rule::enum(PayslipLineType::class)],
            'is_taxable' => ['required', 'boolean'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'amount.not_in' => 'Le montant de l\'ajustement ne peut pas être nul.',
            'type.Illuminate\Validation\Rules\Enum' => 'Le type d\'ajustement est invalide.',
        ];
    }

    public function authorize(): bool
    {
        $payslip = $this->route('payslip');

        // On ne peut modifier que si la paie est en brouillon
        return $payslip && $payslip->status === PayrollStatus::Draft;
    }
}
