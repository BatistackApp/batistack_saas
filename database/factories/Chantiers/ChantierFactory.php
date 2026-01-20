<?php

namespace Database\Factories\Chantiers;

use App\Enums\Chantiers\ChantierStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChantierFactory extends Factory
{
    protected $model = Chantier::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'code' => 'CHT'.str_pad((string) $this->faker->unique()->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
            'name' => $this->faker->company().' - Projet '.$this->faker->word(),
            'description' => $this->faker->sentence(),
            'status' => ChantierStatus::Planned,
            'start_date' => null,
            'end_date' => null,
            'budget_total' => $this->faker->randomFloat(2, 10000, 500000),

            'tiers_id' => Tiers::factory(),
        ];
    }

    public function active(): self
    {
        return $this->state(fn () => [
            'status' => ChantierStatus::Active,
            'start_date' => now()->subDays(10),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn () => [
            'status' => ChantierStatus::Completed,
            'start_date' => now()->subMonths(3),
            'end_date' => now()->subWeek(),
        ]);
    }
}
