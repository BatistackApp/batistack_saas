<?php

namespace App\Http\Requests\Articles;

use Illuminate\Foundation\Http\FormRequest;

class InventoryLineRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'inventory_session_id' => ['required', 'exists:inventory_sessions'],
            'article_id' => ['required', 'exists:articles'],
            'theoretical_quantity' => ['required', 'numeric'],
            'counted_quantity' => ['nullable', 'numeric'],
            'difference' => ['required', 'numeric'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
