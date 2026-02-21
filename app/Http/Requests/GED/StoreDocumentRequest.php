<?php

namespace App\Http\Requests\GED;

use App\Enums\GED\DocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480', // 20 Mo
                'mimes:pdf,jpg,jpeg,png,webp,docx,xlsx,zip,dwg', // Ajout du DWG pour les plans BTP
            ],
            'type' => ['required', new Enum(DocumentType::class)],
            'folder_id' => [
                'nullable',
                Rule::exists('document_folders', 'id')->where('tenants_id', $this->user()->tenants_id),
            ],
            'expires_at' => ['nullable', 'date', 'after:today'],
            'description' => ['nullable', 'string', 'max:500'],

            // Validation pour la relation polymorphique (optionnel lors de l'upload)
            'documentable_type' => ['nullable', 'string'],
            'documentable_id' => ['nullable', 'integer'],

            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'Le fichier ne doit pas dépasser 20 Mo.',
            'file.mimes' => 'Format de fichier non supporté (PDF, Images, Office, Zip et DWG uniquement).',
            'type.required' => 'Le type de document est obligatoire pour la conformité.',
            'expires_at.after' => 'La date d\'expiration doit être une date future.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
