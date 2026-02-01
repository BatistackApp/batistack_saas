<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class OuvrageRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants'],
            'sku' => ['required'],
            'name' => ['required'],
            'description' => ['nullable'],
            'unit' => ['required'],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
