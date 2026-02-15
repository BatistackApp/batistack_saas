<?php

namespace Database\Factories\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Enums\Fleet\FinesStatus;
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
        $offense = fake()->dateTimeBetween('-1 year', 'now');
        $project_imputable = $this->faker->boolean();
        return [
            'notice_number' => 'AB-'.fake()->numerify('###').'-CD',
            'offense_at' => $offense,
            'location' => $this->faker->address(),
            'amount_initial' => $this->faker->randomFloat(),
            'amount_discounted' => $this->faker->randomFloat(),
            'amount_increased' => $this->faker->randomFloat(),
            'due_date' => $offense->add(new \DateInterval('P45D')),
            'status' => $this->faker->randomElement(FinesStatus::cases()),
            'designation_status' => $this->faker->randomElement(DesignationStatus::cases()),
            'is_project_imputable' => $project_imputable,
            'document_path' => $this->faker->filePath(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'vehicle_id' => Vehicle::factory(),
            'fine_category_id' => FineCategory::factory(),
            'user_id' => User::factory(),
            'project_id' => $project_imputable ? Project::factory() : null,
        ];
    }
}
