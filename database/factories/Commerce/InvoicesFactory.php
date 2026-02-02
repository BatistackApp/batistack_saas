<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InvoicesFactory extends Factory
{
    protected $model = Invoices::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->word(),
            'reference' => $this->faker->word(),
            'situation_number' => $this->faker->word(),
            'total_ht' => $this->faker->randomFloat(),
            'total_tva' => $this->faker->randomFloat(),
            'total_ttc' => $this->faker->randomFloat(),
            'retenue_garantie_pct' => $this->faker->randomFloat(),
            'retenue_garantie_amount' => $this->faker->randomFloat(),
            'compte_prorata_amount' => $this->faker->randomFloat(),
            'is_autoliquidation' => $this->faker->boolean(),
            'status' => $this->faker->word(),
            'due_date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'tiers_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'quote_id' => Quote::factory(),
        ];
    }
}
