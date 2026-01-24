<?php

namespace Database\Factories\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingEntryFactory extends Factory
{
    protected $model = AccountingEntry::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'posted_at' => Carbon::now(),
            'description' => $this->faker->text(),
            'status' => $this->faker->randomElement(EntryStatus::cases()),
            'total_debit' => $this->faker->randomFloat(),
            'total_credit' => $this->faker->randomFloat(),
            'source_type' => $this->faker->word(),
            'source_id' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'accounting_journal_id' => AccountingJournal::factory(),
        ];
    }
}
