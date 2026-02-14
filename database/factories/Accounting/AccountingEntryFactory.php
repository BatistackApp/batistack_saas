<?php

namespace Database\Factories\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\Journal;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingEntryFactory extends Factory
{
    protected $model = AccountingEntry::class;

    public function definition(): array
    {
        return [
            'journal_id' => Journal::factory(),
            'reference_number' => fake()->unique()->numerify('VE/20250101/####'),
            'accounting_date' => fake()->dateTime(),
            'label' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'total_debit' => fake()->randomFloat(4, 100, 10000),
            'total_credit' => fake()->randomFloat(4, 100, 10000),
            'status' => EntryStatus::Draft,
            'created_by' => User::factory(),
        ];
    }
}
