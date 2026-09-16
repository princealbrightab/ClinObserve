<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class StudentProfileFactory extends Factory
{
    public function definition(): array
    {
        return ['user_id' => User::factory(), 'roll_number' => fake()->unique()->bothify('MED-####??'), 'batch' => 'Group A', 'academic_year' => '2026–2027', 'college' => 'Demonstration Medical College', 'course' => 'MBBS', 'department' => 'General Medicine', 'joining_year' => 2024];
    }
}
