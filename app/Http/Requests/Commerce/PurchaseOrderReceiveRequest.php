<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseOrderReceiveRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'warehouse_id' => 'required|exists:warehouses,id',
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity' => 'required|numeric|gt:0',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
