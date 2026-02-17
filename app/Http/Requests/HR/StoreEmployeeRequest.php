<?php

namespace App\Http\Requests\HR;

use Illuminate\Foundation\Http\FormRequest;

class StoreEmployeeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'exists:users,id'],
            'external_id' => ['nullable', 'string', 'max:255'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'hourly_cost_charged' => ['required', 'numeric', 'min:0'],
            'contract_type' => ['required', 'string', 'max:255'],
            'contract_end_date' => ['nullable', 'date', 'after_or_equal:hired_at'],
            'hired_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
            'email' => ['required', 'email', 'string', 'unique:users,email'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user()->hasRole('tenant_admin');
    }
}
