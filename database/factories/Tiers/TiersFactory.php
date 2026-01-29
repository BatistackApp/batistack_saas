<?php

namespace Database\Factories\Tiers;

use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TiersFactory extends Factory
{
    protected $model = Tiers::class;

    public function definition(): array
    {
        return [
            'code_tiers' => $this->faker->word(),
            'type_entite' => $this->faker->word(),
            'raison_social' => $this->faker->word(),
            'nom' => $this->faker->word(),
            'prenom' => $this->faker->word(),
            'adresse' => $this->faker->word(),
            'code_postal' => $this->faker->postcode(),
            'ville' => $this->faker->word(),
            'pays' => $this->faker->word(),
            'telephone' => $this->faker->word(),
            'email' => $this->faker->unique()->safeEmail(),
            'site_web' => $this->faker->word(),
            'siret' => $this->faker->word(),
            'numero_tva' => $this->faker->word(),
            'code_naf' => $this->faker->word(),
            'iban' => $this->faker->word(),
            'bic' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
