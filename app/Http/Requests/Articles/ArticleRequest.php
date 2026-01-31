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
            'tenants_id' => ['required', 'exists:tenants,id'],
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

            // Nouveaux identifiants (Recommandation ProBTP)
            'barcode' => ['nullable', 'string', 'max:100'],
            'qr_code_base' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('articles', 'qr_code_base')->ignore($articleId)
            ],

            // Propriétés physiques (Recommandation ProBTP)
            'poids' => ['nullable', 'numeric', 'min:0'],
            'volume' => ['nullable', 'numeric', 'min:0'],

            'purchase_price_ht' => ['nullable', 'numeric', 'min:0'],
            'cump_ht' => ['nullable', 'numeric', 'min:0'],
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
            'qr_code_base.unique' => 'Cet identifiant QR Code est déjà assigné.',
        ];
    }

    public function authorize(): bool
    {
        // Pour la création
        if ($this->isMethod('POST')) {
            return $this->user()->can('inventory.manage');
        }
        // Pour la mise à jour
        return $this->user()->can('inventory.manage') && $this->route('article')->tenants_id === $this->user()->tenants_id;
    }
}
