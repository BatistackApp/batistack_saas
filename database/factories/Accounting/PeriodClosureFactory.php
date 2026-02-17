<?php

namespace Database\Factories\Accounting;

use App\Models\Accounting\PeriodClosure;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PeriodClosureFactory extends Factory
{
    protected $model = PeriodClosure::class;

    public function definition(): array
    {
        $year = Carbon::now();

        return [
            'ulid' => Str::ulid(),
            'month' => $this->faker->month(),
            'year' => $year,
            'period_start' => $year->startOfYear()->toDateString(),
            'period_end' => $year->endOfYear()->toDateString(),
            'is_locked' => $this->faker->boolean(),
            'closed_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'tenants_id' => Tenants::factory(),
            'closed_by' => User::factory(),
        ];
    }
}
