<?php

namespace Database\Factories\Tier;

use App\Models\Core\Tenants;
use App\Models\Tiers\TierEquipement;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TierEquipementFactory extends Factory
{
    protected $model = TierEquipement::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'brand' => $this->faker->word(),
            'model' => $this->faker->word(),
            'serial_number' => $this->faker->word(),
            'installation_date' => Carbon::now(),
            'warranty_expiration_date' => Carbon::now(),
            'technical_data' => $this->faker->words(),
            'location_details' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'customer_id' => Tiers::factory(),
        ];
    }
}
