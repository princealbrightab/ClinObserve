<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    public function definition(): array
    {
        return ['display_name' => 'Synthetic case '.fake()->unique()->numerify('####'), 'data_classification' => 'synthetic', 'gender' => 'undisclosed', 'age_years' => 42, 'admission_date' => now()->subDays(30)->toDateString(), 'chief_complaint' => 'Fictional case for documentation practice.', 'created_by' => User::factory(), 'updated_by' => fn (array $a) => $a['created_by']];
    }
}
