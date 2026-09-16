<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientEncounterFactory extends Factory
{
    public function definition(): array
    {
        return ['patient_id' => Patient::factory(), 'student_id' => User::factory(), 'attended_at' => now()->subHour(), 'summary' => 'Synthetic observation for academic documentation practice.', 'learning_notes' => 'Review the structure of a clear history and discuss omissions with faculty.'];
    }
}
