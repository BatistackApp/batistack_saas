<?php

namespace App\Http\Requests\Pilotage;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiManualSnapshotRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'kpi_indicator_id' => [
                'required',
                Rule::exists('kpi_indicators', 'id')->where(fn ($q) => $q->where('tenants_id', auth()->user()->tenants_id))
            ],
            'context_id' => ['nullable', 'string'], // ULID d'un projet par exemple
            'context_type' => ['nullable', 'string'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('pilotage.manage');
    }
}
