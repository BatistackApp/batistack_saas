<?php

namespace App\Http\Requests\Commerce;

use App\Enums\Commerce\PurchaseOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PurchaseOrderRequest extends FormRequest
{
    public function rules(): array
    {
        $orderId = $this->route('purchase_order')?->id;
        return [
            'tenants_id' => ['required', 'exists:tenants,id'],
            'supplier_id' => ['required', 'exists:tiers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'reference' => [
                'required',
                'string',
                'max:50',
                Rule::unique('purchase_orders')->ignore($orderId),
            ],
            'status' => ['required', Rule::enum(PurchaseOrderStatus::class)],
            'order_date' => ['required', 'date'],
            'expected_delivery_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'notes' => ['nullable', 'string'],
            'created_by' => ['nullable', 'exists:users,id'],

            // Validation des items de commande
            'items' => ['required', 'array', 'min:1'],
            'items.*.article_id' => ['nullable', 'exists:articles,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price_ht' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'expected_delivery_date.after_or_equal' => 'La date de livraison prévue ne peut pas être antérieure à la date de commande.',
            'items.*.article_id.exists' => 'Un des articles sélectionnés est introuvable.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
