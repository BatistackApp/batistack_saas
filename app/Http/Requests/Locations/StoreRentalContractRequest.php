<?php

namespace App\Http\Requests\Locations;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRentalContractRequest extends FormRequest
{
    public function rules(): array
    {
        $tenantId = $this->user()->tenants_id;

        return [
            // On vérifie que le fournisseur appartient au tenant
            'provider_id' => [
                'required',
                Rule::exists('tiers', 'id')->where('tenants_id', $tenantId),
            ],
            // On vérifie que le projet appartient au tenant
            'project_id' => [
                'required',
                Rule::exists('projects', 'id')->where('tenants_id', $tenantId),
            ],
            'project_phase_id' => [
                'nullable',
                Rule::exists('project_phases', 'id')
                    ->where('project_id', $this->project_id), // La phase est liée au projet
            ],
            'label' => ['required', 'string', 'max:255'],
            'start_date_planned' => ['required', 'date', 'after_or_equal:today'],
            'end_date_planned' => [
                'nullable',
                'date',
                'after_or_equal:start_date_planned',
            ],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider_id.exists' => "Le fournisseur sélectionné est invalide ou n'appartient pas à votre organisation.",
            'project_id.exists' => "Le chantier sélectionné est invalide ou n'appartient pas à votre organisation.",
            'start_date_planned.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'end_date_planned.after_or_equal' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('locations.manage');
    }
}
