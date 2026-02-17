<?php

namespace Database\Factories\HR;

use App\Models\HR\TimeEntry;
use App\Models\HR\TimeEntryLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class TimeEntryLogFactory extends Factory
{
    protected $model = TimeEntryLog::class;

    public function definition(): array
    {
        return [
            'from_status' => $this->faker->word(),
            'to_status' => $this->faker->word(),
            'comment' => $this->faker->word(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'time_entry_id' => TimeEntry::factory(),
            'user_id' => User::factory(),
        ];
    }
}
