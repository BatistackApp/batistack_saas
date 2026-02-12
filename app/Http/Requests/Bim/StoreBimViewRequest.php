<?php

namespace App\Http\Requests\Bim;

use Illuminate\Foundation\Http\FormRequest;

class StoreBimViewRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'bim_model_id' => ['required', 'exists:bim_models,id'],
            'name' => ['required', 'string', 'max:100'],
            // Structure de l'état de la caméra Three.js
            'camera_state' => ['required', 'array'],
            'camera_state.position' => ['required', 'array'],
            'camera_state.position.x' => ['required', 'numeric'],
            'camera_state.position.y' => ['required', 'numeric'],
            'camera_state.position.z' => ['required', 'numeric'],
            'camera_state.target' => ['required', 'array'],
            'camera_state.target.x' => ['required', 'numeric'],
            'camera_state.target.y' => ['required', 'numeric'],
            'camera_state.target.z' => ['required', 'numeric'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('bim.view');
    }
}
