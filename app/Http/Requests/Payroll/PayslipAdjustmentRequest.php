<?php

namespace App\Http\Requests\Payroll;

use App\Enums\Payroll\PayrollStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PayslipAdjustmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'not_in:0'],
            'type' => ['required', Rule::in(['earning', 'deduction'])],
            'is_taxable' => ['required', 'boolean'],
        ];
    }

    public function authorize(): bool
    {
        $payslip = $this->route('payslip');

        // On ne peut modifier que si la paie est en brouillon
        return $payslip && $payslip->status === PayrollStatus::Draft;
    }
}
