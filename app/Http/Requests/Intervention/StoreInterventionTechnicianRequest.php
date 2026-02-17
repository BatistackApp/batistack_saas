<?php

namespace App\Http\Requests\Intervention;

use Illuminate\Foundation\Http\FormRequest;

class StoreInterventionTechnicianRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'employee_id' => ['required', 'exists:employees,id'],
            'hours_spent' => ['required', 'numeric', 'min:0.25'], // Minimum 15 minutes
        ];
    }

    public function authorize(): bool
    {
        $intervention = $this->route('intervention');

        return $intervention && $intervention->status === \App\Enums\Intervention\InterventionStatus::InProgress;
    }
}
