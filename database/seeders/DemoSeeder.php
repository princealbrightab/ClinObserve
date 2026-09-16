<?php

namespace Database\Seeders;

use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use App\UserRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo seeding is restricted to local/testing environments.');
        }
        DB::transaction(function (): void {
            $hod = User::firstOrCreate(['email' => 'hod@clinobserve.test'], ['name' => 'Dr. Meera Raman', 'password' => Hash::make('FacultyDemo123!')]);
            if (! $hod->wasRecentlyCreated) {
                $this->command?->info('Demo accounts already exist; seeding skipped to preserve records.');

                return;
            }
            $hod->role = UserRole::Hod;
            $hod->is_active = true;
            $hod->must_change_password = false;
            $hod->save();
            $students = [];
            foreach (['Ananya Sharma', 'Arjun Nair', 'Diya Patel', 'Kabir Shah', 'Ishaan Rao', 'Sara Thomas'] as $index => $name) {
                $student = User::create(['name' => $name, 'email' => 'student'.($index + 1).'@clinobserve.test', 'password' => Hash::make('StudentDemo123!')]);
                $student->role = UserRole::Student;
                $student->is_active = true;
                $student->must_change_password = false;
                $student->created_by = $hod->id;
                $student->save();
                $student->studentProfile()->create(['roll_number' => 'MED-2026-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 'batch' => $index < 3 ? 'Group A' : 'Group B', 'academic_year' => '2026–2027', 'college' => 'Demonstration Medical College', 'course' => 'MBBS', 'department' => 'General Medicine', 'joining_year' => 2024, 'bio' => 'Medical student learning through careful observation and faculty reflection.']);
                $students[] = $student;
            }
            $topics = ['Respiratory history', 'Cardiovascular examination', 'Abdominal history', 'Neurological documentation', 'Musculoskeletal examination', 'General clinical communication', 'Allergy history', 'Medication reconciliation'];
            foreach ($topics as $i => $topic) {
                $patient = Patient::factory()->create(['display_name' => 'Learning case '.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT), 'age_years' => 25 + $i * 5, 'gender' => $i % 2 ? 'male' : 'female', 'condition' => $topic, 'admission_date' => now()->subDays(45)->toDateString(), 'chief_complaint' => 'Synthetic scenario for practicing '.strtolower($topic).'.', 'history_of_present_illness' => 'Fictional educational record. No real patient is represented.', 'created_by' => $students[$i % 6]->id, 'updated_by' => $students[$i % 6]->id]);
                for ($visit = 0; $visit < 3; $visit++) {
                    $entry = PatientEncounter::factory()->create(['patient_id' => $patient->id, 'student_id' => $students[($i + $visit) % 6]->id, 'attended_at' => now()->subDays(($i * 3 + $visit) % 22)->subHours(2), 'summary' => 'Practiced '.strtolower($topic).'. Recorded the provided history in chronological order and identified areas requiring clarification.', 'symptoms_observed' => 'Documented the fictional symptoms supplied for this academic exercise.', 'examination_findings' => 'Recorded examination sections and distinguished supplied findings from missing information.', 'assessment' => 'Academic reflection only. Discuss interpretation with the supervising faculty.', 'learning_notes' => 'Review relevant terminology and improve the completeness of the written observation.']);
                    if ($visit === 0) {
                        $entry->locked_at = now();
                        $entry->save();
                        $review = $entry->hodReviews()->make(['comment' => 'Clear structure and useful reflection. Add a more precise timeline and explicitly note information that was not provided.']);
                        $review->hod_id = $hod->id;
                        $review->save();
                    }
                }
            }
        });
    }
}
