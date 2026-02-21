<?php

namespace App\Http\Requests\Locations;

use App\Enums\Locations\RentalInspectionType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRentalInspectionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(RentalInspectionType::class)],
            'notes' => ['nullable', 'string'],
            'photos' => ['required', 'array', 'min:1'],
            'photos.*' => ['image', 'max:10240'], // 10 Mo pour la HD chantier

            // Ajouts pour la valeur juridique (Recommandation 6)
            'client_signature' => ['nullable', 'string'], // Base64 de la signature
            'provider_signature' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Au moins une photo est obligatoire pour valider l\'état des lieux.',
            'photos.min' => 'Vous devez prendre au moins une photo du matériel.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('locations.manage');
    }
}
