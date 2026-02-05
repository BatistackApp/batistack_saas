<?php

namespace Database\Factories\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TimeEntryFactory extends Factory
{
    protected $model = TimeEntry::class;

    public function definition(): array
    {
        return [
            'tenants_id' => Tenants::factory(),
            'employee_id' => Employee::factory(),
            'project_id' => Project::factory(),
            'project_phase_id' => ProjectPhase::factory(),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now')->format('Y-m-d'),
            'hours' => $this->faker->randomElement([7.0, 7.5, 8.0, 8.5, 10.0]),
            'status' => $this->faker->randomElement(TimeEntryStatus::cases()),
            'has_meal_allowance' => $this->faker->boolean(80), // 80% de chance d'avoir un panier
            'has_host_allowance' => $this->faker->boolean(10), // 10% de chance d'avoir une prime zone/logement
            'travel_time' => $this->faker->randomFloat(2, 0, 2.5),
            'notes' => $this->faker->optional()->sentence(),
            'verified_by' => fn (array $attributes) => $attributes['status'] === TimeEntryStatus::Approved ? User::factory() : null,
        ];
    }

    /**
     * État spécifique pour un pointage en brouillon.
     */
    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TimeEntryStatus::Draft,
            'verified_by' => null,
        ]);
    }

    /**
     * État spécifique pour un pointage approuvé.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TimeEntryStatus::Approved,
            'verified_by' => User::factory(),
        ]);
    }
}
