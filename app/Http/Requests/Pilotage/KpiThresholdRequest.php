<?php

namespace App\Http\Requests\Pilotage;

use App\Enums\Pilotage\ThresholdSeverity;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiThresholdRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kpi_indicator_id' => [
                'required',
                Rule::exists('kpi_indicators', 'id')->where(fn ($q) => $q->where('tenants_id', auth()->user()->tenants_id)),
            ],
            'min_value' => ['nullable', 'numeric'],
            'max_value' => [
                'nullable',
                'numeric',
                'greater_than_field:min_value', // Règle personnalisée ou logique manuelle requise
            ],
            'severity' => ['required', Rule::enum(ThresholdSeverity::class)],
            'is_notifiable' => ['boolean'],
        ];
    }

    /**
     * Logique de validation supplémentaire pour croiser min et max.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $min = $this->input('min_value');
            $max = $this->input('max_value');

            if ($min !== null && $max !== null && $min >= $max) {
                $validator->errors()->add('max_value', 'La valeur maximum doit être strictement supérieure à la valeur minimum.');
            }

            if ($min === null && $max === null) {
                $validator->errors()->add('min_value', 'Vous devez définir au moins un seuil (minimum ou maximum).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'kpi_indicator_id.exists' => "L'indicateur sélectionné n'existe pas ou ne vous appartient pas.",
            'severity.required' => "Le niveau de sévérité de l'alerte est obligatoire.",
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('pilotage.manage');
    }
}
