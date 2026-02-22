<?php

namespace App\Http\Requests\Intervention;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInterventionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            // 1. Informations qualitatives obligatoires (Preuve métier)
            'report_notes' => ['required', 'string', 'min:20'],
            'client_signature' => ['required', 'string'], // Image base64 ou token de signature

            // 2. Horodatage réel
            'completed_at' => ['required', 'date', 'before_or_equal:now'],

            // 3. Récapitulatif final de la main d'œuvre (pour les TimeEntries RH)
            'technicians' => ['required', 'array', 'min:1'],
            'technicians.*.employee_id' => ['required', 'exists:employees,id'],
            'technicians.*.hours_spent' => ['required', 'numeric', 'min:0.25'],

            // 4. Photos (Optionnel mais recommandé dans le BTP)
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:10240'], // Max 10Mo par photo
        ];
    }

    public function messages(): array
    {
        return [
            'report_notes.required' => 'Le compte-rendu technique est obligatoire pour clôturer le dossier.',
            'report_notes.min' => 'Le compte-rendu doit être suffisamment détaillé (20 caractères min).',
            'client_signature.required' => 'La signature du client est requise pour valider l\'intervention.',
            'technicians.required' => 'Au moins un technicien doit être saisi avec son temps passé.',
        ];
    }

    public function authorize(): bool
    {
        $intervention = $this->route('intervention');

        return $this->user()->can('intervention.manage') ||
            $intervention->technicians()->where('user_id', $this->user()->id)->exists();
    }
}
