<?php

namespace Database\Factories\Core;

use App\Models\Core\BillingHistory;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BillingHistoryFactory extends Factory
{
    protected $model = BillingHistory::class;

    public function definition(): array
    {
        return [
            'event_type' => $this->faker->word(),
            'old_plan_id' => $this->faker->word(),
            'new_plan_id' => $this->faker->word(),
            'amount_charged' => $this->faker->randomFloat(),
            'currency' => $this->faker->word(),
            'description' => $this->faker->text(),
            'stripe_subscription_id' => $this->faker->word(),
            'stripe_invoice_id' => $this->faker->word(),
            'metadata' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
