<?php

namespace App\Http\Requests\Projects;

use App\Enums\Projects\ProjectStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:tiers,id'], // Client obligatoire (Module Tiers)
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'initial_budget_ht' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'planned_start_at' => ['nullable', 'date'],
            'planned_end_at' => [
                'nullable',
                'date',
                'after_or_equal:planned_start_at' // Cohérence temporelle
            ],
            'budget_labor' => 'required|numeric|min:0',
            'budget_materials' => 'required|numeric|min:0',
            'budget_subcontracting' => 'required|numeric|min:0',
            'budget_site_overheads' => 'required|numeric|min:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function messages(): array
    {
        return [
            'planned_end_at.after_or_equal' => 'La date de fin prévisionnelle ne peut pas être antérieure à la date de début.',
            'initial_budget_ht.min' => 'Le budget initial doit être un montant positif.',
        ];
    }
}
