<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\AbsenceRequestStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class AbsenceRequestReviewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', new Enum(AbsenceRequestStatus::class)],
            'rejection_reason' => [
                'required_if:status,'.AbsenceRequestStatus::Rejected->value,
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('payroll.manage');
    }
}
