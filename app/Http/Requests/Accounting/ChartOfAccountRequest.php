<?php

namespace App\Http\Requests\Accounting;

use App\Enums\Accounting\AccountType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChartOfAccountRequest extends FormRequest
{
    public function rules(): array
    {
        $accountId = $this->route('chart_of_account')?->id;

        return [
            'account_number' => [
                'required',
                'string',
                'max:20',
                // Unicité par tenant
                Rule::unique('chart_of_accounts', 'account_number')
                    ->where('tenants_id', auth()->user()->tenants_id)
                    ->ignore($accountId)
            ],
            'account_label' => ['required', 'string', 'max:255'],
            'account_type' => ['required', 'string', 'max:100'],
            'nature' => ['required', Rule::enum(AccountType::class)],
            'is_active' => ['boolean'],
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('accounting.manage');
    }
}
