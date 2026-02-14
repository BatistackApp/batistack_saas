<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingEntryLineFactory extends Factory
{
    protected $model = AccountingEntryLine::class;

    public function definition(): array
    {
        return [
            'accounting_entry_id' => AccountingEntry::factory(),
            'chart_of_account_id' => ChartOfAccount::factory(),
            'debit' => fake()->randomFloat(4, 0, 5000),
            'credit' => 0,
            'description' => fake()->sentence(),
            'line_order' => 0,
        ];
    }
}
