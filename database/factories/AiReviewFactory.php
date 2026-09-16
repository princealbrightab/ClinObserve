<?php

namespace Database\Factories;

use App\Models\PatientEncounter;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class AiReviewFactory extends Factory
{
    public function definition(): array
    {
        return ['encounter_id' => PatientEncounter::factory(), 'requested_by' => fn (array $a) => PatientEncounter::findOrFail($a['encounter_id'])->student_id, 'request_key' => (string) Str::uuid(), 'provider' => 'test', 'model' => 'test-model', 'prompt_version' => 'education-v1', 'status' => 'failed', 'input_snapshot' => ['summary' => 'Synthetic test input'], 'input_hash' => hash('sha256', 'synthetic'), 'privacy_confirmed_at' => now(), 'completed_at' => now(), 'error_code' => 'test_failure'];
    }
}
