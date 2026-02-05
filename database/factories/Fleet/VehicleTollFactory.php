<?php

namespace Database\Factories\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleToll;
use App\Models\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleTollFactory extends Factory
{
    protected $model = VehicleToll::class;

    public function definition(): array
    {
        return [
            'entry_at' => Carbon::now(),
            'exit_at' => Carbon::now(),
            'entry_station' => $this->faker->word(),
            'exit_station' => $this->faker->word(),
            'amount_ht' => $this->faker->randomFloat(),
            'external_transaction_id' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'vehicle_id' => Vehicle::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
