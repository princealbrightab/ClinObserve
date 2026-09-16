<?php

namespace Database\Factories;

use App\Models\PatientEncounter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EncounterImageFactory extends Factory
{
    public function definition(): array
    {
        return ['encounter_id' => PatientEncounter::factory(), 'uploaded_by' => fn (array $a) => PatientEncounter::findOrFail($a['encounter_id'])->student_id, 'file_path' => 'encounters/'.Str::uuid().'.png', 'mime_type' => 'image/png', 'file_size' => 68];
    }
}
