<?php

namespace App\Http\Requests\GED;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFolderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                // Unicité au sein du même dossier parent pour ce tenant
                Rule::unique('document_folders')->where(function ($query) {
                    return $query->where('tenants_id', $this->user()->tenants_id)
                        ->where('parent_id', $this->parent_id);
                }),
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('document_folders', 'id')->where('tenants_id', $this->user()->tenants_id),
            ],
            'color' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
