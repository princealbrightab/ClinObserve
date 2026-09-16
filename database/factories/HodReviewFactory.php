<?php

namespace Database\Factories;

use App\Models\PatientEncounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HodReviewFactory extends Factory
{
    public function definition(): array
    {
        return ['encounter_id' => PatientEncounter::factory(), 'hod_id' => User::factory()->hod(), 'comment' => 'Good documentation. Clarify the timeline and distinguish observations from interpretation.'];
    }
}
