<?php

namespace App\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;

class StoreRentalContractRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'provider_id' => ['required', 'exists:tiers,id'],
            'project_id' => ['required', 'exists:projects,id'],
            'project_phase_id' => ['nullable', 'exists:project_phases,id'],
            'label' => ['required', 'string', 'max:255'],
            'start_date_planned' => ['required', 'date', 'after_or_equal:today'],
            'end_date_planned' => ['nullable', 'date', 'after_or_equal:start_date_planned'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('locations.manage');
    }
}
