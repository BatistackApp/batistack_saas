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
                // Unicité du nom dans le même dossier parent pour ce tenant
                Rule::unique('folders')->where(function ($query) {
                    return $query->where('tenant_id', $this->user()->tenant_id)
                        ->where('parent_id', $this->parent_id);
                })
            ],
            'parent_id' => [
                'nullable',
                Rule::exists('folders', 'id')->where('tenant_id', $this->user()->tenant_id)
            ],
            'color' => 'nullable|string|regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
