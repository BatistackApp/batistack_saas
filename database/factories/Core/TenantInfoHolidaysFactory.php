<?php

namespace Database\Factories\Core;

use App\Models\Core\TenantInfoHolidays;
use App\Models\Core\Tenants;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TenantInfoHolidaysFactory extends Factory
{
    protected $model = TenantInfoHolidays::class;

    public function definition(): array
    {
        return [
            'date' => Carbon::now(),
            'label' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
