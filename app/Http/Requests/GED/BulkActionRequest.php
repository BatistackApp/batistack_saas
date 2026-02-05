<?php

namespace App\Http\Requests\GED;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkActionRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'document_ids' => 'required|array',
            'document_ids.*' => [
                Rule::exists('documents', 'id')->where('tenant_id', $this->user()->tenant_id)
            ],
            'action' => 'required|in:move,delete,archive',
            'target_folder_id' => [
                'required_if:action,move',
                'nullable',
                Rule::exists('folders', 'id')->where('tenant_id', $this->user()->tenant_id)
            ]
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
