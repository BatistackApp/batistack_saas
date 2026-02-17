<?php

namespace App\Http\Requests\Accounting;

use App\Enums\Accounting\JournalType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class JournalRequest extends FormRequest
{
    public function rules(): array
    {
        $journalId = $this->route('journal')?->id;

        return [
            'code' => [
                'required',
                'string',
                'max:3',
                Rule::unique('journals', 'code')
                    ->where('tenants_id', auth()->user()->tenants_id)
                    ->ignore($journalId),
            ],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(JournalType::class)],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('accounting.manage');
    }
}
