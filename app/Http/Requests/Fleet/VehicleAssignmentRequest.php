<?php

namespace App\Http\Requests\Fleet;

use Illuminate\Foundation\Http\FormRequest;

class VehicleAssignmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
