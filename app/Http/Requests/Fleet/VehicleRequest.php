<?php

namespace App\Http\Requests\Fleet;

use App\Enums\Fleet\FuelType;
use App\Enums\Fleet\VehicleType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'internal_code' => [
                'required',
                'string',
                Rule::unique('vehicles')->ignore($vehicleId)->where('tenants_id', $this->user()->tenants_id),
            ],
            'type' => ['required', Rule::enum(VehicleType::class)],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'fuel_type' => ['required', Rule::enum(FuelType::class)],

            // Intégrations API (Optionnel)
            'external_fuel_card_id' => ['nullable', 'string', 'max:100'],
            'external_toll_tag_id' => ['nullable', 'string', 'max:100'],

            // Tarification analytique
            'hourly_rate' => ['required', 'numeric', 'min:0'],
            'km_rate' => ['required', 'numeric', 'min:0'],

            // Compteurs
            'current_odometer' => ['required', 'numeric', 'min:0'],
            'odometer_unit' => ['required', 'in:km,h'],

            'purchase_date' => ['nullable', 'date'],
            'purchase_price' => ['nullable', 'numeric', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
