<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingSequence;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingSequenceFactory extends Factory
{
    protected $model = AccountingSequence::class;

    public function definition(): array
    {
        return [
            'year' => $this->faker->randomNumber(),
            'next_number' => $this->faker->randomNumber(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'accounting_journal_id' => AccountingJournal::factory(),
        ];
    }
}
