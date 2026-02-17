<?php

namespace App\Http\Requests\Intervention;

use App\Enums\Intervention\InterventionStatus;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInterventionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'planned_at' => ['sometimes', 'date'],
            'warehouse_id' => ['sometimes', 'exists:warehouses,id'],
        ];
    }

    public function authorize(): bool
    {
        $intervention = $this->route('intervention');

        // On ne peut modifier l'en-tête que si l'intervention n'est pas encore terminée
        return $intervention && in_array($intervention->status, [
            InterventionStatus::Planned,
            InterventionStatus::InProgress,
        ]);
    }
}
