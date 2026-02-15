<?php

namespace App\Http\Requests\Fleet;

use App\Enums\Fleet\MaintenanceType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenanceRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'vehicle_maintenance_plan_id' => ['nullable', 'exists:vehicle_maintenance_plans,id'],
            'maintenance_type' => ['required', Rule::enum(MaintenanceType::class)],
            'description' => ['required', 'string'],
            'scheduled_at' => ['nullable', 'date', 'after_or_equal:today'],
            'technician_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
