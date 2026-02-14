<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\ChartOfAccount;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ChartOfAccountFactory extends Factory
{
    protected $model = ChartOfAccount::class;

    public function definition(): array
    {
        return [
            'account_number' => fake()->unique()->numerify('###000'),
            'account_label' => fake()->words(3, true),
            'account_type' => fake()->numberBetween(1, 7),
            'nature' => fake()->randomElement(AccountType::cases()),
            'is_active' => true,
        ];
    }
}
