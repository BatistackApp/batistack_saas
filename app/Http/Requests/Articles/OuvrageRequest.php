<?php

namespace App\Http\Requests\Articles;

use App\Enums\Articles\ArticleUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OuvrageRequest extends FormRequest
{
    public function rules(): array
    {
        if ($this->isMethod('post') || $this->isMethod('put')) {
            $ouvrageId = $this->route('ouvrage')?->id;

            return [
                'tenants_id' => ['required', 'exists:tenants,id'],
                'sku' => [
                    'required',
                    'string',
                    'max:50',
                    Rule::unique('ouvrages', 'sku')->ignore($ouvrageId),
                ],
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string'],
                'unit' => ['required', Rule::enum(ArticleUnit::class)],
                'is_active' => ['boolean'],
            ];
        }

        return [];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Cette référence d\'ouvrage est déjà utilisée.',
            'unit.Illuminate\Validation\Rules\Enum' => 'L\'unité de mesure de l\'ouvrage est invalide.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
