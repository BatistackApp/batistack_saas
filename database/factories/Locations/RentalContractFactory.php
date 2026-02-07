<?php

namespace Database\Factories\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Core\Tenants;
use App\Models\Locations\RentalContract;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RentalContractFactory extends Factory
{
    protected $model = RentalContract::class;

    public function definition(): array
    {
        return [
            'reference' => 'LOC-' . $this->faker->unique()->numberBetween(1000, 9999),
            'label' => 'Location ' . $this->faker->word(),
            'start_date_planned' => now()->addDays(1),
            'end_date_planned' => now()->addDays(15),
            'actual_pickup_at' => Carbon::now(),
            'actual_return_at' => Carbon::now(),
            'status' => $this->faker->randomElement(RentalStatus::cases()),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'provider_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
        ];
    }
}
