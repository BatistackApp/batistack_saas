<?php

namespace Database\Factories\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\AbsenceType;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AbsenceRequestFactory extends Factory
{
    protected $model = AbsenceRequest::class;

    public function definition(): array
    {
        $starts = now()->addDays(rand(1, 30));

        return [
            'employee_id' => Employee::factory(),
            'type' => fake()->randomElement(AbsenceType::cases()),
            'status' => AbsenceRequestStatus::Pending,
            'starts_at' => $starts,
            'ends_at' => (clone $starts)->addDays(rand(1, 5)),
            'duration_days' => rand(1, 5),
            'reason' => fake()->sentence(),
        ];
    }
}
