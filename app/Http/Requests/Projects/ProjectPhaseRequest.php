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
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
