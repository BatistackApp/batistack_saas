<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingEntryLineFactory extends Factory
{
    protected $model = AccountingEntryLine::class;

    public function definition(): array
    {
        return [
            'debit' => $this->faker->randomFloat(),
            'credit' => $this->faker->randomFloat(),
            'description' => $this->faker->text(),
            'analytical_code' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'accounting_entry_id' => AccountingEntry::factory(),
            'accounting_accounts_id' => AccountingAccounts::factory(),
        ];
    }
}
