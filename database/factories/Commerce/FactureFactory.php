<?php

namespace Database\Factories\Commerce;

use App\Models\Chantiers\Chantier;
use App\Models\Commerce\Devis;
use App\Models\Commerce\Facture;
use App\Models\Commerce\Situation;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class FactureFactory extends Factory
{
    protected $model = Facture::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->word(),
            'date_facture' => Carbon::now(),
            'date_echeance' => Carbon::now(),
            'type' => $this->faker->word(),
            'montant_ht' => $this->faker->randomFloat(),
            'montant_tva' => $this->faker->randomFloat(),
            'montant_ttc' => $this->faker->randomFloat(),
            'montant_paye' => $this->faker->word(),
            'status' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'tiers_id' => Tiers::factory(),
            'chantier_id' => Chantier::factory(),
            'devis_id' => Devis::factory(),
            'situation_id' => Situation::factory(),
        ];
    }
}
