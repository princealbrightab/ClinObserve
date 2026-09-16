<?php

namespace Tests\Feature;

use App\Models\EncounterImage;
use App\Models\HodReview;
use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ClinicalWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function observation(): array
    {
        return ['attended_at' => now(config('clinobserve.timezone'))->subHour()->format('Y-m-d\\TH:i'), 'summary' => 'Documented a synthetic clinical observation.', 'learning_notes' => 'Review with faculty.'];
    }

    public function test_student_creates_case_and_repeated_encounters_without_automatic_ai_calls(): void
    {
        Http::preventStrayRequests();
        $student = User::factory()->create();
        $this->actingAs($student)->post(route('patients.store'), ['display_name' => 'Synthetic A', 'data_classification' => 'synthetic', 'gender' => 'undisclosed', 'age_years' => 35, 'admission_date' => now()->subDay()->toDateString(), 'privacy_confirmed' => 1])->assertSessionHasNoErrors()->assertRedirect();
        $patient = Patient::firstOrFail();
        $this->assertSame($student->id, $patient->created_by);
        $this->assertStringStartsWith('CASE-', $patient->case_number);
        $this->post(route('encounters.store', $patient), $this->observation())->assertSessionHasNoErrors()->assertRedirect();
        $this->post(route('encounters.store', $patient), $this->observation())->assertSessionHasNoErrors();
        $this->assertDatabaseCount('patient_encounters', 2);
        $this->assertSame(1, $student->encounters()->distinct()->count('patient_id'));
        Http::assertNothingSent();
    }

    public function test_invalid_case_and_spoofed_ownership_are_rejected(): void
    {
        $student = User::factory()->create();
        $patient = Patient::factory()->create();
        $this->actingAs($student)->post(route('patients.store'), ['display_name' => 'Bad', 'age_years' => 150])->assertSessionHasErrors(['age_years', 'gender', 'admission_date', 'privacy_confirmed']);
        $this->post(route('encounters.store', $patient), $this->observation() + ['student_id' => 999])->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('patient_encounters', 0);
    }

    public function test_student_cannot_edit_another_students_encounter(): void
    {
        $entry = PatientEncounter::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($other)->patch(route('encounters.update', $entry), $this->observation())->assertForbidden();
        $this->assertSame('Synthetic observation for academic documentation practice.', $entry->fresh()->summary);
    }

    public function test_owner_can_edit_until_faculty_review_locks_encounter(): void
    {
        $entry = PatientEncounter::factory()->create();
        $hod = User::factory()->hod()->create();
        $this->actingAs($entry->student)->patch(route('encounters.update', $entry), $this->observation())->assertSessionHasNoErrors();
        $this->assertSame('Documented a synthetic clinical observation.', $entry->fresh()->summary);
        $this->actingAs($hod)->post(route('hod.reviews.store', $entry), ['comment' => 'Private faculty guidance.'])->assertSessionHasNoErrors();
        $this->assertNotNull($entry->fresh()->locked_at);
        $this->assertDatabaseHas('hod_reviews', ['encounter_id' => $entry->id, 'hod_id' => $hod->id, 'comment' => 'Private faculty guidance.']);
        $this->actingAs($entry->student)->patch(route('encounters.update', $entry), $this->observation())->assertForbidden();
    }

    public function test_shared_timeline_never_leaks_private_faculty_feedback(): void
    {
        $entry = PatientEncounter::factory()->create();
        $review = HodReview::factory()->create(['encounter_id' => $entry->id, 'comment' => 'PRIVATE FACULTY SECRET']);
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('encounters.show', $entry))->assertSee($entry->summary)->assertDontSee('PRIVATE FACULTY SECRET');
        $this->get(route('patients.show', $entry->patient))->assertDontSee('PRIVATE FACULTY SECRET');
        $this->get(route('hod-reviews.show', $review))->assertForbidden();
        $this->actingAs($entry->student)->get(route('hod-reviews.show', $review))->assertSee('PRIVATE FACULTY SECRET');
    }

    public function test_images_are_validated_reencoded_and_protected(): void
    {
        Storage::fake('clinical');
        $entry = PatientEncounter::factory()->create();
        $this->actingAs($entry->student)->post(route('encounter-images.store', $entry), ['images' => [UploadedFile::fake()->image('private.jpg')]])->assertSessionHasNoErrors();
        $image = $entry->images()->firstOrFail();
        Storage::disk('clinical')->assertExists($image->file_path);
        $this->assertSame($entry->student_id, $image->uploaded_by);
        $this->assertSame('image/png', $image->mime_type);
        $this->get(route('encounter-images.show', $image))->assertOk()->assertHeader('content-type', 'image/png');
        $this->post(route('encounter-images.store', $entry), ['images' => [UploadedFile::fake()->create('bad.svg', 1, 'image/svg+xml')]])->assertSessionHasErrors('images.0');
        $this->post(route('logout'));
        $this->get(route('encounter-images.show', $image))->assertRedirect(route('login'));
    }

    public function test_total_image_limit_and_other_student_removal_are_enforced(): void
    {
        Storage::fake('clinical');
        $entry = PatientEncounter::factory()->create();
        EncounterImage::factory()->count(5)->create(['encounter_id' => $entry->id]);
        $this->actingAs($entry->student)->post(route('encounter-images.store', $entry), ['images' => [UploadedFile::fake()->image('extra.png')]])->assertSessionHasErrors('images');
        $this->actingAs(User::factory()->create())->delete(route('encounter-images.destroy', $entry->images()->first()))->assertForbidden();
        $this->assertDatabaseCount('encounter_images', 5);
    }

    public function test_student_calendar_rejects_another_student_filter(): void
    {
        $student = User::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($student)->get(route('calendar.index', ['student_id' => $other->id]))->assertSessionHasErrors('student_id');
    }

    public function test_calendar_uses_institution_day_boundaries(): void
    {
        $this->travelTo(now()->setDate(2026, 9, 15)->setTime(12, 0));
        $entry = PatientEncounter::factory()->create(['attended_at' => '2026-09-14 20:00:00']);
        $this->actingAs($entry->student)->get(route('calendar.index', ['month' => '2026-09', 'date' => '2026-09-15']))->assertSee('1 patients')->assertSee($entry->patient->display_name);
        $this->get(route('calendar.index', ['month' => '2026-09', 'date' => '2026-09-14']))->assertSee('0 patients');
    }

    public function test_all_student_pages_render_and_escape_content(): void
    {
        $entry = PatientEncounter::factory()->create(['summary' => '<script>alert(1)</script> academic note']);
        $this->actingAs($entry->student)->get(route('encounters.show', $entry))->assertSee('<script>alert(1)</script>')->assertDontSee('<script>alert(1)</script>', false);
        foreach (['dashboard', 'patients.index', 'patients.create', 'encounters.index', 'calendar.index', 'ai-reviews.index', 'profile.edit'] as $route) {
            $this->get(route($route))->assertOk();
        }
        $this->get(route('patients.show', $entry->patient))->assertOk();
        $this->get(route('encounters.create', $entry->patient))->assertOk();
        $this->get(route('encounters.edit', $entry))->assertOk();
    }

    public function test_case_edits_are_restricted_after_first_encounter(): void
    {
        $entry = PatientEncounter::factory()->create();
        $creator = $entry->patient->creator;
        $this->actingAs($creator)->get(route('patients.edit',$entry->patient))->assertForbidden();
        $this->actingAs(User::factory()->hod()->create())->get(route('patients.edit',$entry->patient))->assertOk();
    }
}
