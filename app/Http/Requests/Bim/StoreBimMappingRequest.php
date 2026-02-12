<?php

namespace App\Http\Requests\Bim;

use Illuminate\Foundation\Http\FormRequest;

class StoreBimMappingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bim_object_id' => ['required', 'exists:bim_objects,id'],
            'mappable_id' => ['required', 'integer'],
            'mappable_type' => ['required', 'string'], // ex: App\Models\Articles\Article
            'color_override' => ['nullable', 'string', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'color_override.regex' => 'Le code couleur doit être au format HEX (ex: #FF0000).',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('bim.manage');
    }
}
