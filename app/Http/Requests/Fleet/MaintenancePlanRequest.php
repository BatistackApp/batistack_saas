<?php

namespace App\Http\Requests\Fleet;

use App\Enums\Fleet\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MaintenancePlanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'vehicle_type' => ['required', Rule::enum(VehicleType::class)],
            'interval_km' => ['nullable', 'integer', 'min:0'],
            'interval_hours' => ['nullable', 'integer', 'min:0'],
            'interval_month' => ['nullable', 'integer', 'min:0'],
            'operations' => ['nullable', 'array'],
            'operations.*' => ['string'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
