<?php

namespace Database\Factories\Fleet;

use App\Models\Core\Tenants;
use App\Models\Fleet\FineCategory;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleFine;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class VehicleFineFactory extends Factory
{
    protected $model = VehicleFine::class;

    public function definition(): array
    {
        return [
            'notice_number' => $this->faker->word(),
            'offense_at' => Carbon::now(),
            'location' => $this->faker->word(),
            'amount_initial' => $this->faker->randomFloat(),
            'amount_discounted' => $this->faker->randomFloat(),
            'amount_increased' => $this->faker->randomFloat(),
            'due_date' => Carbon::now(),
            'status' => $this->faker->word(),
            'designation_status' => $this->faker->word(),
            'is_project_imputable' => $this->faker->boolean(),
            'document_path' => $this->faker->word(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'vehicle_id' => Vehicle::factory(),
            'fine_category_id' => FineCategory::factory(),
            'user_id' => User::factory(),
            'project_id' => Project::factory(),
        ];
    }
}
