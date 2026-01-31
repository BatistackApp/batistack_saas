<?php

namespace App\Http\Requests\Articles;

use App\Enums\Articles\ArticleUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        $articleId = $this->route('article')?->id;
        return [
            'tenants_id' => ['required', 'exists:tenants, id'],
            'category_id' => ['nullable', 'exists:article_categories,id'],
            'default_supplier_id' => ['nullable', 'exists:tiers,id'],
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('articles', 'sku')->ignore($articleId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unit' => ['required', Rule::enum(ArticleUnit::class)],

            'purchase_price_ht' => ['required', 'numeric', 'min:0'],
            'cump_ht' => ['required', 'numeric', 'min:0'],
            'sale_price_ht' => ['required', 'numeric', 'min:0'],

            'min_stock' => ['required', 'numeric', 'min:0'],
            'alert_stock' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'Cette référence (SKU) est déjà utilisée par un autre article.',
            'unit.Illuminate\Validation\Rules\Enum' => 'L\'unité de mesure sélectionnée est invalide.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
