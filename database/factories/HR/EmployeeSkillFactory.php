<?php

namespace Database\Factories\HR;

use App\Models\HR\Employee;
use App\Models\HR\EmployeeSkill;
use App\Models\HR\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class EmployeeSkillFactory extends Factory
{
    protected $model = EmployeeSkill::class;

    public function definition(): array
    {
        return [
            'issue_date' => Carbon::now(),
            'expiry_date' => Carbon::now(),
            'reference_number' => $this->faker->word(),
            'document_path' => $this->faker->word(),
            'level' => $this->faker->randomNumber(),
            'notes' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'employee_id' => Employee::factory(),
            'skill_id' => Skill::factory(),
        ];
    }
}
