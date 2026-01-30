<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectPhaseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects'],
            'name' => ['required'],
            'allocated_budget' => ['required', 'numeric'],
            'order' => ['required', 'integer'],
            'status' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
