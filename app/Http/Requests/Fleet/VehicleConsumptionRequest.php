<?php

namespace App\Http\Requests\Fleet;

use App\Rules\Fleet\AfterCurrentOdometerRule;
use Illuminate\Foundation\Http\FormRequest;

class VehicleConsumptionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'quantity' => ['required', 'numeric', 'gt:0'],
            'amount_ht' => ['required', 'numeric', 'min:0'],
            'odometer_reading' => [
                'required',
                'numeric',
                new AfterCurrentOdometerRule($this->vehicle_id),
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
