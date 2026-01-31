<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class ArticleRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenants_id' => ['required', 'exists:tenants, id'],
            'category_id' => ['nullable', 'exists:article_categories,id'],
            'default_supplier_id' => ['nullable', 'exists:tiers,id'],
            'sku' => ['required'],
            'name' => ['required'],
            'description' => ['nullable'],
            'unit' => ['required'],
            'purchase_price_ht' => ['required', 'numeric'],
            'cump_ht' => ['required', 'numeric'],
            'sale_price_ht' => ['required', 'numeric'],
            'min_stock' => ['required', 'numeric'],
            'alert_stock' => ['required', 'numeric'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
