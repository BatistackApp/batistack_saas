<?php

namespace Database\Factories\Bim;

use App\Enums\Bim\BimModelStatus;
use App\Models\Bim\BimModel;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class BimModelFactory extends Factory
{
    protected $model = BimModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'file_path' => $this->faker->word(),
            'version' => $this->faker->randomNumber(),
            'status' => $this->faker->randomElement(BimModelStatus::cases()),
            'file_size' => $this->faker->randomNumber(),
            'metadata' => $this->faker->words(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
