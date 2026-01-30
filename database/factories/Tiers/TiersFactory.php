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
            'code_tiers' => $this->faker->unique()->regexify('[A-Z]{3}-[0-9]{6}'),
            'type_entite' => $this->faker->randomElement(['personne_morale', 'personne_physique']),
            'raison_social' => $this->faker->company(),
            'nom' => $this->faker->lastName,
            'prenom' => $this->faker->firstName,
            'adresse' => $this->faker->streetAddress,
            'code_postal' => $this->faker->postcode(),
            'ville' => $this->faker->city,
            'pays' => $this->faker->countryCode,
            'telephone' => $this->faker->phoneNumber,
            'email' => $this->faker->unique()->safeEmail(),
            'site_web' => $this->faker->url(),
            'siret' => $this->faker->numerify('##############'),
            'numero_tva' => $this->faker->numerify('FR##############'),
            'code_naf' => $this->faker->numerify('####Z'),
            'iban' => $this->faker->iban,
            'bic' => $this->faker->swiftBicNumber,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
