<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'customer_id' => ['required', 'exists:tiers'],
            'code_project' => ['required'],
            'name' => ['required'],
            'description' => ['nullable'],
            'address' => ['nullable'],
            'latitude' => ['nullable'],
            'longitude' => ['nullable'],
            'initial_budget_ht' => ['required', 'numeric'],
            'status' => ['required'],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => ['nullable', 'date'],
            'actual_start_at' => ['nullable', 'date'],
            'actual_end_at' => ['nullable', 'date'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
