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
        $amountHt = $this->faker->randomFloat(2, 200, 5000);
        $materialCost = $amountHt * 0.3;
        $laborCost = $amountHt * 0.4;
        $margin = $amountHt - ($materialCost + $laborCost);

        return [
            'reference' => 'INT-' . $this->faker->unique()->numberBetween(10000, 99999),
            'label' => 'Maintenance ' . $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status' => InterventionStatus::Planned,
            'billing_type' => BillingType::Regie,
            'planned_at' => now()->addDays(rand(1, 10)),

            'amount_ht' => $amountHt,
            'material_cost_ht' => $materialCost,
            'labor_cost_ht' => $laborCost,
            'margin_ht' => $margin,

            'customer_id' => Tiers::factory(),
            'tenants_id' => Tenants::factory(),
        ];
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => InterventionStatus::Completed,
            'report_notes' => 'Problème résolu. Remplacement de la vanne effectué.',
            'client_signature' => 'data:image/png;base64,iVBORw0KGgoAAAAN...',
            'client_name' => $this->faker->name(),
            'completed_at' => now(),
        ]);
    }
}
