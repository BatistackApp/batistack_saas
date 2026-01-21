<?php

namespace Database\Factories\Commerce;

use App\Models\Commerce\Avenant;
use App\Models\Commerce\Commande;
use App\Models\Core\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class AvenantFactory extends Factory
{
    protected $model = Avenant::class;

    public function definition(): array
    {
        return [
            'number' => $this->faker->word(),
            'date_avenant' => Carbon::now(),
            'description' => $this->faker->text(),
            'montant_ht' => $this->faker->randomFloat(),
            'montant_tva' => $this->faker->randomFloat(),
            'montant_ttc' => $this->faker->randomFloat(),
            'status' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenant_id' => Tenant::factory(),
            'commande_id' => Commande::factory(),
        ];
    }
}
