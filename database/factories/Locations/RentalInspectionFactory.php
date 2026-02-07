<?php

namespace Database\Factories\Locations;

use App\Enums\Locations\RentalInspectionType;
use App\Models\Locations\RentalContract;
use App\Models\Locations\RentalInspection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class RentalInspectionFactory extends Factory
{
    protected $model = RentalInspection::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(RentalInspectionType::cases()),
            'notes' => $this->faker->word(),
            'photos' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'rental_contract_id' => RentalContract::factory(),
            'inspector_id' => User::factory(),
        ];
    }
}
