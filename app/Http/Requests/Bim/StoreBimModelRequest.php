<?php

namespace App\Http\Requests\Bim;

use Illuminate\Foundation\Http\FormRequest;

class StoreBimModelRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            // Validation du fichier IFC (souvent volumineux)
            'ifc_file' => ['required', 'file', 'max:102400'], // Limite à 100 Mo par défaut
            'metadata' => ['nullable', 'array'],
        ];
    }

    public function messages(): array
    {
        return [
            'ifc_file.required' => 'Le fichier de la maquette numérique (IFC) est obligatoire.',
            'ifc_file.max' => 'Le fichier est trop volumineux (maximum 100 Mo).',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('bim.manage');
    }
}
