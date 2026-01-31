<?php

namespace App\Http\Requests\Projects;

use Illuminate\Foundation\Http\FormRequest;

class ProjectAmendmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects'],
            'reference' => ['required'],
            'description' => ['nullable'],
            'amount_ht' => ['required', 'numeric'],
            'status' => ['required'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
