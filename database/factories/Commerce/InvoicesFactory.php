<?php

namespace Database\Factories\Commerce;

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoicesFactory extends Factory
{
    protected $model = Invoices::class;

    public function definition(): array
    {
        $totalHt = $this->faker->randomFloat(2, 1000, 10000);
        $totalTva = $totalHt * 0.2;

        return [
            'tenants_id' => Tenants::factory(),
            'tiers_id' => Tiers::factory(),
            'project_id' => Project::factory(),
            'quote_id' => Quote::factory(),
            'type' => InvoiceType::Progress,
            'reference' => 'FAC-'.$this->faker->unique()->numberBetween(10000, 99999),
            'situation_number' => $this->faker->numberBetween(1, 5),
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalHt + $totalTva,
            'retenue_garantie_pct' => 5.00,
            'retenue_garantie_amount' => $totalHt * 0.05,
            'compte_prorata_amount' => 0,
            'is_autoliquidation' => false,
            'status' => InvoiceStatus::Draft,
            'due_date' => now()->addDays(30),
        ];
    }
}
