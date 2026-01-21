<?php

namespace Database\Factories\Commerce;

use App\Models\Chantiers\Chantier;
use App\Models\Commerce\Devis;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class DevisFactory extends Factory
{
    protected $model = Devis::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->word(),
            'date_emission' => Carbon::now(),
            'date_validite' => Carbon::now(),
            'montant_ht' => $this->faker->randomFloat(),
            'montant_tva' => $this->faker->randomFloat(),
            'montant_ttc' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'tiers_id' => Tiers::factory(),
            'chantier_id' => Chantier::factory(),
        ];
    }
}
