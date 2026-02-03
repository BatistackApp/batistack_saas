<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\QuoteStatus;
use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'customer_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'reference' => 'DEV-'.$this->faker->unique()->numberBetween(10000, 99999),
            'status' => QuoteStatus::Draft,
            'total_ht' => 0,
            'total_tva' => 0,
            'total_ttc' => 0,
            'valid_until' => now()->addMonths(1),
        ];
    }
}
