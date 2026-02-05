<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'first_name' => ['sometimes', 'string', 'max:255'],
            'last_name' => ['sometimes', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'hourly_cost_charged' => ['sometimes', 'numeric', 'min:0'],
            'contract_type' => ['sometimes', 'string', 'max:255'],
            'contract_end_date' => ['nullable', 'date'],
            'hired_at' => ['sometimes', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
