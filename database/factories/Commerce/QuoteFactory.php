<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'reference' => $this->faker->word(),
            'status' => $this->faker->word(),
            'total_ht' => $this->faker->randomFloat(),
            'total_tva' => $this->faker->randomFloat(),
            'total_ttc' => $this->faker->randomFloat(),
            'valid_until' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'customer_id' => Tiers::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
