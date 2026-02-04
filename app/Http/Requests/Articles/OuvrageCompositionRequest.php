<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class OuvrageCompositionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'article_id' => ['required', 'exists:articles,id'],
            'quantity_needed' => ['required', 'numeric', 'gt:0'],
            'wastage_factor_pct' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'article_id.exists' => 'L\'article sélectionné n\'existe pas dans le catalogue.',
            'quantity_needed.gt' => 'La quantité nécessaire doit être supérieure à zéro.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
