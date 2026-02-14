<?php

namespace App\Http\Requests\Pilotage;

use App\Enums\Pilotage\KpiCategory;
use App\Enums\Pilotage\KpiUnit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class KpiIndicatorRequest extends FormRequest
{
    public function rules(): array
    {
        $indicatorId = $this->route('kpi_indicator')?->id;
        $tenantId = auth()->user()->tenants_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'required',
                'string',
                'max:50',
                // Unicité du code par tenant
                Rule::unique('kpi_indicators')->where(fn ($q) => $q->where('tenants_id', $tenantId))->ignore($indicatorId)
            ],
            'description' => ['nullable', 'string', 'max:1000'],
            'category' => ['required', Rule::enum(KpiCategory::class)],
            'unit' => ['required', Rule::enum(KpiUnit::class)],
            'formula_class' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.unique' => "Ce code d'indicateur est déjà utilisé pour votre entreprise.",
            'category.Illuminate\Validation\Rules\Enum' => "La catégorie sélectionnée n'est pas valide.",
            'unit.Illuminate\Validation\Rules\Enum' => "L'unité de mesure sélectionnée n'est pas valide.",
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('pilotage.manage');
    }
}
