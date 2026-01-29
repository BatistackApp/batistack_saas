<?php

namespace Database\Factories\Core;

use App\Enums\Core\TenantStatus;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantsFactory extends Factory
{
    protected $model = Tenants::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->slug(),
            'email' => $this->faker->email(),
            'database' => $this->faker->word(),
            'domain' => $this->faker->word(),
            'status' => $this->faker->randomElement(TenantStatus::cases()),
            'settings' => $this->faker->words(),
            'activated_at' => Carbon::now(),
            'suspended_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
