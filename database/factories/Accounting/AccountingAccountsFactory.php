<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\AccountingAccounts;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AccountingAccountsFactory extends Factory
{
    protected $model = AccountingAccounts::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->word(),
            'name' => $this->faker->name(),
            'type' => $this->faker->word(),
            'is_active' => $this->faker->boolean(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
        ];
    }
}
