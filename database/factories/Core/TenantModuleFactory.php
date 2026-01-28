<?php

namespace Database\Factories\Core;

use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantModuleFactory extends Factory
{
    protected $model = TenantModule::class;

    public function definition(): array
    {
        return [
            'status' => $this->faker->word(),
            'starts_at' => Carbon::now(),
            'ends_at' => Carbon::now(),
            'config' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'module_id' => ModuleCatalog::factory(),
        ];
    }
}
