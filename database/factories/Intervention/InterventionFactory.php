<?php

namespace Database\Factories\Intervention;

use App\Enums\Intervention\BillingType;
use App\Enums\Intervention\InterventionStatus;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Intervention\Intervention;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InterventionFactory extends Factory
{
    protected $model = Intervention::class;

    public function definition(): array
    {
        return [
            'reference' => 'INT-' . $this->faker->unique()->numberBetween(1000, 9999),
            'label' => 'Intervention ' . $this->faker->word(),
            'description' => $this->faker->text(),
            'planned_at' => Carbon::now(),
            'status' => $this->faker->randomElement(InterventionStatus::cases()),
            'billing_type' => $this->faker->randomElement(BillingType::cases()),
            'amount_ht' => $this->faker->randomFloat(),
            'amount_cost_ht' => $this->faker->randomFloat(),
            'margin_ht' => $this->faker->randomFloat(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'customer_id' => Tiers::factory(),
            'warehouse_id' => Warehouse::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
        ];
    }
}
