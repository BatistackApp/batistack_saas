<?php

namespace App\Http\Requests\Projects;

use App\Enums\Projects\ProjectPhaseStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectPhaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'allocated_budget' => ['required', 'numeric', 'min:0'],
            'order' => ['required', 'integer', 'min:0'],
            'status' => ['required', Rule::enum(ProjectPhaseStatus::class)],
            'depends_on_phase_id' => 'nullable|exists:project_phases,id',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $project = \App\Models\Projects\Project::find($this->project_id);
            if ($project) {
                $currentTotal = $project->phases()->sum('allocated_budget');
                if (($currentTotal + $this->allocated_budget) > $project->internal_target_budget_ht) {
                    $validator->errors()->add('allocated_budget', 'Dépassement du budget interne global du chantier.');
                }
            }
        });
    }

    public function authorize(): bool
    {
        return true;
    }
}
