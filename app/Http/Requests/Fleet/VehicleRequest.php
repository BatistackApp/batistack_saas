<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class VehicleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'name' => ['required'],
            'internal_code' => ['required'],
            'type' => ['required'],
            'license_plate' => ['nullable'],
            'brand' => ['nullable'],
            'model' => ['nullable'],
            'vin' => ['nullable'],
            'fuel_type' => ['required'],
            'external_fuel_card_id' => ['nullable'],
            'external_toll_tag_id' => ['nullable'],
            'hourly_rate' => ['required', 'numeric'],
            'km_rate' => ['required', 'numeric'],
            'current_odometer' => ['nullable', 'numeric'],
            'odometer_unit' => ['required'],
            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric'],
            'last_external_sync_at' => ['nullable', 'date'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
