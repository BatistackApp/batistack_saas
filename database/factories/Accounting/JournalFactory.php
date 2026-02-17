<?php

namespace Database\Factories\Accounting;

use App\Enums\Accounting\JournalType;
use App\Models\Accounting\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'code' => fake()->randomElement(['VE', 'AC', 'BQ', 'OD', 'PA']),
            'label' => fake()->word(),
            'type' => fake()->randomElement(JournalType::cases()),
            'is_active' => true,
        ];
    }
}
