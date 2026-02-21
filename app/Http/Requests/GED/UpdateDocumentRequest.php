<?php

namespace App\Http\Requests\GED;

use App\Enums\GED\DocumentStatus;
use App\Enums\GED\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'type' => ['sometimes', 'required', new Enum(DocumentType::class)],
            'status' => ['sometimes', 'required', new Enum(DocumentStatus::class)],
            'expires_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:500'],
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function authorize(): bool
    {
        return $this->document && $this->document->tenants_id === $this->user()->tenants_id;
    }
}
