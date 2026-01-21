<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\Avoir;
use App\Models\Commerce\Facture;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AvoirFactory extends Factory
{
    protected $model = Avoir::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->word(),
            'date_avoir' => Carbon::now(),
            'motif' => $this->faker->word(),
            'montant_ht' => $this->faker->randomFloat(),
            'montant_tva' => $this->faker->randomFloat(),
            'montant_ttc' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'facture_id' => Facture::factory(),
        ];
    }
}
