<?php

namespace App\Http\Requests\Fleet;

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
                // On peut ajouter une règle personnalisée ici pour vérifier
                // que l'odomètre est cohérent avec le relevé précédent
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
