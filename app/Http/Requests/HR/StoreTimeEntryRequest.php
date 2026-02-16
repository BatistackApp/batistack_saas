<?php

namespace App\Http\Requests\HR;

use App\Enums\HR\TimeEntryStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreTimeEntryRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants,id'],
            'employee_id' => ['required', 'exists:employees,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'date' => ['required', 'date'],
            'hours' => ['required', 'numeric', 'min:0', 'max:24'],
            'status' => ['sometimes', new Enum(TimeEntryStatus::class)],
            'has_meal_allowance' => ['boolean'],
            'has_host_allowance' => ['boolean'],
            'travel_time' => ['numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->user() && $this->user()->tenants_id) {
            $this->merge([
                'tenants_id' => $this->user()->tenants_id,
            ]);
        }
    }
}
