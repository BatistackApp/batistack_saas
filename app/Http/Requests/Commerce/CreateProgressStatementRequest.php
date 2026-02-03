<?php

namespace App\Http\Requests\Commerce;

use Illuminate\Foundation\Http\FormRequest;

class CreateProgressStatementRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'quote_id' => 'required|exists:quotes,id',
            'situation_number' => 'required|integer',
            'progress_data' => 'required|array',
            'progress_data.*.quote_item_id' => 'required|exists:quote_items,id',
            'progress_data.*.progress_percentage' => 'required|numeric|between:0,100',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
