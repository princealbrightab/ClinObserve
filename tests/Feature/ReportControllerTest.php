<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ReportControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_report_counts_unique_cases_and_months_for_selected_student(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 15)->setTime(12, 0));
        $hod = User::factory()->hod()->create();
        $student = User::factory()->create();
        $patient = Patient::factory()->create(['admission_date' => '2026-08-01']);
        PatientEncounter::factory()->count(2)->create(['student_id' => $student->id, 'patient_id' => $patient->id, 'attended_at' => '2026-09-10 08:00:00']);
        PatientEncounter::factory()->create(['student_id' => $student->id, 'patient_id' => $patient->id, 'attended_at' => '2026-08-10 08:00:00']);
        PatientEncounter::factory()->create(['patient_id' => $patient->id, 'attended_at' => '2026-09-11 08:00:00']);
        $this->actingAs($hod)->get(route('hod.reports.index', ['from' => '2026-09-01', 'to' => '2026-09-15', 'student_id' => $student->id]))
            ->assertOk()->assertViewHas('students', fn ($rows) => (int) $rows->first()->encounters_count === 2 && (int) $rows->first()->patients_count === 1)
            ->assertViewHas('monthly', fn ($months) => $months === ['Sep 2026' => 2]);
    }

    public function test_report_rejects_reversed_or_excessive_date_range(): void
    {
        $this->actingAs(User::factory()->hod()->create())->get(route('hod.reports.index', ['from' => '2026-09-15', 'to' => '2026-09-01']))->assertSessionHasErrors('to');
        $this->get(route('hod.reports.index', ['from' => '2000-01-01', 'to' => '2026-09-15']))->assertSessionHasErrors('to');
    }
}
