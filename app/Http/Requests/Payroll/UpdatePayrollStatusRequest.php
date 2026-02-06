<?php

namespace App\Http\Requests\Payroll;

use App\Enums\Payroll\PayrollStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePayrollStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(PayrollStatus::class)],
            'confirm_lock' => ['required_if:status,' . PayrollStatus::Validated->value, 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirm_lock.accepted' => 'Vous devez confirmer le verrouillage définitif des pointages pour valider la paie.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
