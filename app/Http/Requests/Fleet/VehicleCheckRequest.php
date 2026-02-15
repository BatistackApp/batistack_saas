<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class VehicleCheckRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id'            => ['required', 'exists:vehicles,id'],
            'vehicle_assignment_id' => ['nullable', 'exists:vehicle_assignments,id'],
            'type'                  => ['required', 'string', 'in:start,end'], // start: Prise de poste, end: Fin de poste
            'odometer_reading'      => ['required', 'numeric', 'min:0'],
            'general_note'          => ['nullable', 'string'],

            // Validation des résultats de la check-list
            'results'                       => ['required', 'array', 'min:1'],
            'results.*.question_id'         => ['required', 'exists:vehicle_checklist_questions,id'],
            'results.*.value'               => ['required', 'string'], // 'ok', 'ko', ou valeur textuelle
            'results.*.anomaly_description' => ['nullable', 'string', 'required_if:results.*.value,ko'],
            'results.*.photo_path'          => ['nullable', 'string'],
        ];
    }

    /**
     * Messages personnalisés pour guider le conducteur.
     */
    public function messages(): array
    {
        return [
            'results.*.anomaly_description.required_if' => 'Un commentaire est obligatoire en cas d\'anomalie détectée.',
            'odometer_reading.required' => 'Le relevé kilométrique est obligatoire pour valider le contrôle.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
