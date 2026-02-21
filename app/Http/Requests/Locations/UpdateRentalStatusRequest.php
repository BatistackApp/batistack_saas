<?php

namespace App\Http\Requests\Locations;

use App\Enums\Locations\RentalStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRentalStatusRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'status' => ['required', Rule::enum(RentalStatus::class)],

            // Date effective de l'action (livraison, retour ou off-hire)
            'actual_date' => ['required', 'date'],

            // Si on passe en OFF_HIRE, on peut demander un numéro de confirmation du loueur
            'off_hire_reference' => [
                Rule::requiredIf($this->status === RentalStatus::OFF_HIRE->value),
                'nullable',
                'string',
                'max:50'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'off_hire_reference.required_if' => "Veuillez saisir la référence de confirmation fournie par le loueur lors de l'appel de reprise.",
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('locations.manage');
    }
}
