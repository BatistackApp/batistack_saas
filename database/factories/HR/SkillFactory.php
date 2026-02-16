<?php

namespace Database\Factories\HR;

use App\Enums\HR\SkillType;
use App\Models\Core\Tenants;
use App\Models\HR\Skill;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'type' => $this->faker->randomElement(SkillType::cases()),
            'description' => $this->faker->text(),
            'requires_expiry' => $this->faker->boolean(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
        ];
    }
}
