<?php

namespace Database\Factories\Articles;

use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'responsible_user_id' => User::factory(),
            'name' => 'Dépôt '.$this->faker->city(),
            'location' => $this->faker->address(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'is_active' => true,
        ];
    }
}
