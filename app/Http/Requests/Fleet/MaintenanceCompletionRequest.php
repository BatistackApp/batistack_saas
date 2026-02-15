<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class MaintenanceCompletionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'completed_at' => ['required', 'date', 'before_or_equal:now'],
            'resolution_notes' => ['required', 'string'],
            'odometer_reading' => ['required', 'numeric', 'min:0'],
            'hours_reading' => ['required', 'numeric', 'min:0'],
            'cost_parts' => ['required', 'numeric', 'min:0'],
            'cost_labor' => ['required', 'numeric', 'min:0'],
            'technician_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
