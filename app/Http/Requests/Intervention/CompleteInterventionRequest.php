<?php

namespace App\Http\Requests\Intervention;

use Illuminate\Foundation\Http\FormRequest;

class CompleteInterventionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'report_notes' => ['required', 'string', 'min:10'], // Obligation de décrire le travail fait
            'completed_at' => ['required', 'date', 'before_or_equal:now'],
            'client_signature' => ['required', 'string'], // Image en base64 de la signature

            // Photos de fin d'intervention (optionnelles mais recommandées)
            'photos' => ['nullable', 'array', 'max:5'],
            'photos.*' => ['image', 'max:5120'],

            // Relevé final des heures si non saisi précédemment
            'technicians' => ['required', 'array', 'min:1'],
            'technicians.*.employee_id' => ['required', 'exists:employees,id'],
            'technicians.*.hours_spent' => ['required', 'numeric', 'min:0.25'],
        ];
    }

    public function messages(): array
    {
        return [
            'report_notes.required' => "Le compte-rendu technique est obligatoire pour clôturer l'intervention.",
            'client_signature.required' => "La signature du client est requise pour valider le bon d'intervention.",
        ];
    }

    public function authorize(): bool
    {
        $intervention = $this->route('intervention');

        return $this->user()->can('intervention.manage') ||
            $intervention->technicians()->where('user_id', $this->user()->id)->exists();
    }
}
