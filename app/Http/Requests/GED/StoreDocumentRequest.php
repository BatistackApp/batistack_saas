<?php

namespace App\Http\Requests\GED;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDocumentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'max:20480', // Limite à 20 Mo par fichier pour préserver le quota global
                'mimes:pdf,jpg,jpeg,png,webp,docx,xlsx,zip'
            ],
            'folder_id' => [
                'nullable',
                Rule::exists('folders', 'id')->where(function ($query) {
                    $query->where('tenants_id', $this->user()->tenants_id);
                }),
            ],
            'description' => 'nullable|string|max:500',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:30'
        ];
    }

    public function messages(): array
    {
        return [
            'file.max' => 'Le fichier ne doit pas dépasser 20 Mo.',
            'file.mimes' => 'Format de fichier non supporté (PDF, Images, Office et Zip uniquement).',
            'folder_id.exists' => 'Le dossier de destination est invalide ou inaccessible.'
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
