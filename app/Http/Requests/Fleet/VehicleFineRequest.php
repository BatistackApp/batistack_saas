<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleFineRequest extends FormRequest
{
    public function rules(): array
    {
        $fineId = $this->route('fine')?->id;

        return [
            // Le véhicule doit exister et appartenir au même tenant (sécurité SaaS)
            'vehicle_id' => [
                'required',
                Rule::exists('vehicles', 'id')->where(function ($query) {
                    $query->where('tenants_id', $this->user()->tenants_id);
                }),
            ],

            // Le chauffeur est optionnel à la saisie mais obligatoire pour l'export ANTAI
            'user_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('tenants_id', $this->user()->tenants_id);
                }),
            ],

            // Numéro de l'avis de contravention (souvent 10 à 12 chiffres)
            'notice_number' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicle_fines', 'notice_number')->ignore($fineId),
            ],

            // Détails de l'infraction
            'offense_at' => ['required', 'date', 'before_or_equal:now'],
            'amount_initial' => ['required', 'numeric', 'min:0'],
            'points_lost' => ['nullable', 'integer', 'min:0', 'max:12'],

            // Localisation et type
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:100'], // ex: Excès de vitesse, Stationnement

            // Note interne
            'notes' => ['nullable', 'string'],
            'tenants_id' => ['required', 'exists:tenants,id'],
        ];
    }

    /**
     * Personnalisation des messages d'erreur.
     */
    public function messages(): array
    {
        return [
            'notice_number.unique' => 'Ce numéro de contravention a déjà été enregistré dans le système.',
            'offence_date.before_or_equal' => 'La date de l\'infraction ne peut pas être dans le futur.',
            'vehicle_id.exists' => 'Le véhicule sélectionné est invalide ou n\'appartient pas à votre entreprise.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
