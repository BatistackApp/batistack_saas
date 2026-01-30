<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects'],
            'user_id' => ['required', 'exists:users'],
            'role' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
