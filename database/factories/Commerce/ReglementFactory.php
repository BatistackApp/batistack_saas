<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\Facture;
use App\Models\Commerce\Reglement;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ReglementFactory extends Factory
{
    protected $model = Reglement::class;

    public function definition(): array
    {
        return [
            'reference_paiement' => $this->faker->word(),
            'date_paiement' => Carbon::now(),
            'montant' => $this->faker->randomFloat(),
            'type_paiement' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'facture_id' => Facture::factory(),
        ];
    }
}
