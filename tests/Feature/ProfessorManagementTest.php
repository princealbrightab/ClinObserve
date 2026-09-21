<?php

namespace Tests\Feature;

use App\Models\AiReview;
use App\Models\EncounterImage;
use App\Models\HodReview;
use App\Models\PatientEncounter;
use App\Models\StudentProfile;
use App\Models\User;
use App\UserRole;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class ProfessorManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hod_creates_and_manages_professor_account(): void
    {
        $hod = User::factory()->hod()->create();
        $this->actingAs($hod)->post(route('hod.professors.store'), ['name' => 'Professor Rao', 'email' => 'rao@example.test', 'password' => 'TemporaryPass123!'])->assertSessionHasNoErrors()->assertRedirect();
        $professor = User::where('email', 'rao@example.test')->firstOrFail();
        $this->assertSame(UserRole::Professor, $professor->role);
        $this->assertSame($hod->id, $professor->created_by);
        $this->assertTrue($professor->must_change_password);
        $this->assertTrue(Hash::check('TemporaryPass123!', $professor->password));
        $this->get(route('hod.professors.index'))->assertSee('Professor Rao');
        $this->get(route('hod.professors.show', $professor))->assertSee('Assigned students');
        $this->get(route('hod.professors.edit', $professor))->assertSee('Edit professor');
        $this->patch(route('hod.professors.update', $professor), ['name' => 'Professor Devi', 'email' => 'devi@example.test'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['id' => $professor->id, 'name' => 'Professor Devi']);
        $this->put(route('hod.professors.credentials.update', $professor), ['password' => 'NewTemporary123!', 'password_confirmation' => 'NewTemporary123!'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewTemporary123!', $professor->fresh()->password));
        $this->patch(route('hod.professors.status.update', $professor), ['is_active' => false])->assertSessionHasNoErrors();
        $this->assertFalse($professor->fresh()->is_active);
    }

    public function test_hod_assigns_reassigns_and_unassigns_student(): void
    {
        $hod = User::factory()->hod()->create();
        $first = User::factory()->professor()->create();
        $second = User::factory()->professor()->create();
        $data = ['name' => 'Assigned Student', 'email' => 'assigned@example.test', 'password' => 'TemporaryPass123!', 'roll_number' => 'MED-001', 'batch' => 'A', 'academic_year' => '2026-2027', 'professor_id' => $first->id];

        $this->actingAs($hod)->post(route('hod.students.store'), $data)->assertSessionHasNoErrors();
        $student = User::where('email', $data['email'])->firstOrFail();
        $this->assertSame($first->id, $student->professor_id);
        unset($data['password']);
        $data['professor_id'] = $second->id;
        $this->patch(route('hod.students.update', $student), $data)->assertSessionHasNoErrors();
        $this->assertSame($second->id, $student->fresh()->professor_id);
        $this->actingAs($first)->get(route('professor.students.show', $student))->assertForbidden();
        $this->actingAs($second)->get(route('professor.students.show', $student))->assertSee('Assigned Student');
        $data['professor_id'] = null;
        $this->actingAs($hod)->patch(route('hod.students.update', $student), $data)->assertSessionHasNoErrors();
        $this->assertNull($student->fresh()->professor_id);
        $this->actingAs($second)->get(route('professor.students.show', $student))->assertForbidden();
    }

    public function test_hod_can_assign_unassigned_students_from_professor_page_and_dashboard_reports_missing_assignments(): void
    {
        $hod = User::factory()->hod()->create();
        $professor = User::factory()->professor()->create(['name' => 'Professor Ashok']);
        $anotherProfessor = User::factory()->professor()->create(['name' => 'Professor Mehta']);
        $assignedStudent = User::factory()->student()->create(['name' => 'Assigned Learner', 'professor_id' => $professor->id]);
        $unassignedStudent = User::factory()->student()->create(['name' => 'Unassigned Learner', 'professor_id' => null]);
        $this->assertNull($unassignedStudent->fresh()->professor_id);

        $this->actingAs($hod)
            ->post(route('hod.professors.assign-students', $professor), ['student_id' => $unassignedStudent->id])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('hod.professors.show', $professor));

        $this->assertSame($professor->id, $unassignedStudent->fresh()->professor_id);
        $this->assertSame($professor->id, $assignedStudent->fresh()->professor_id);

        $this->actingAs($hod)->get(route('hod.dashboard'))
            ->assertViewHas('stats', fn ($stats) => $stats['Students'] === 2 && $stats['Professors'] === 2 && $stats['Unassigned students'] === 0 && $stats['Professors without students'] === 1)
            ->assertSee('Professor Mehta')
            ->assertDontSee('Unassigned Learner');
    }

    #[TestWith(['student', true])]
    #[TestWith(['hod', true])]
    #[TestWith(['professor', false])]
    public function test_assignment_rejects_accounts_that_are_not_active_professors(string $role, bool $active): void
    {
        $invalid = User::factory()->create(['role' => $role, 'is_active' => $active]);
        $this->actingAs(User::factory()->hod()->create())->post(route('hod.students.store'), ['name' => 'Invalid Assignment', 'email' => 'invalid@example.test', 'password' => 'TemporaryPass123!', 'roll_number' => 'MED-001', 'batch' => 'A', 'academic_year' => '2026', 'professor_id' => $invalid->id])->assertSessionHasErrors('professor_id');
        $this->assertDatabaseMissing('users', ['email' => 'invalid@example.test']);
    }

    #[TestWith(['student'])]
    #[TestWith(['professor'])]
    public function test_only_hod_can_manage_accounts(string $role): void
    {
        $user = User::factory()->create(['role' => $role]);
        $professor = User::factory()->professor()->create();
        $student = User::factory()->create(['professor_id' => $professor->id]);
        $this->actingAs($user)->get(route('hod.professors.index'))->assertForbidden();
        $this->post(route('hod.professors.store'), ['name' => 'Injected', 'email' => 'injected@example.test', 'password' => 'TemporaryPass123!'])->assertForbidden();
        $this->patch(route('hod.professors.update', $professor), ['name' => 'Injected'])->assertForbidden();
        $this->patch(route('hod.professors.status.update', $professor), ['is_active' => false])->assertForbidden();
        $this->put(route('hod.professors.credentials.update', $professor), ['password' => 'InjectedPass123!'])->assertForbidden();
        $this->patch(route('hod.students.update', $student), ['professor_id' => $user->id])->assertForbidden();
        $this->assertDatabaseMissing('users', ['email' => 'injected@example.test']);
        $this->assertSame($professor->id, $student->fresh()->professor_id);
        $this->assertTrue($professor->fresh()->is_active);
    }

    public function test_professor_lists_and_shared_patient_timeline_exclude_unassigned_students(): void
    {
        $this->freezeTime();
        $professor = User::factory()->professor()->create();
        $student = User::factory()->create(['professor_id' => $professor->id, 'name' => 'Assigned learner']);
        $assigned = PatientEncounter::factory()->for($student, 'student')->create(['summary' => 'Assigned observation']);
        $other = PatientEncounter::factory()->create(['summary' => 'Hidden observation']);
        $shared = PatientEncounter::factory()->for($assigned->patient)->for($other->student, 'student')->create(['summary' => 'Hidden shared timeline']);
        HodReview::factory()->for($assigned, 'encounter')->create(['comment' => 'Visible faculty suggestion']);
        HodReview::factory()->for($other, 'encounter')->create(['comment' => 'Hidden faculty suggestion']);
        AiReview::factory()->for($assigned, 'encounter')->create();
        AiReview::factory()->for($other, 'encounter')->create();

        $this->actingAs($professor)->get(route('professor.students.index'))->assertSee($student->name)->assertDontSee($other->student->name);
        $this->get(route('encounters.index'))->assertViewHas('encounters', fn ($rows) => $rows->pluck('id')->all() === [$assigned->id]);
        $this->get(route('patients.index'))->assertViewHas('patients', fn ($rows) => $rows->count() === 1 && $rows->first()->encounters_count === 1);
        $this->get(route('patients.show', $assigned->patient))->assertSee('Assigned observation')->assertDontSee('Hidden shared timeline');
        $this->get(route('dashboard'))->assertSee('Visible faculty suggestion')->assertDontSee('Hidden faculty suggestion')->assertDontSee($other->patient->display_name);
        $this->get(route('ai-reviews.index'))->assertViewHas('reviews', fn ($rows) => $rows->count() === 1 && $rows->first()->encounter_id === $assigned->id);
        $this->get(route('calendar.index', ['date' => $assigned->attended_at->timezone(config('clinobserve.timezone'))->toDateString()]))->assertViewHas('entries', fn ($rows) => $rows->pluck('id')->all() === [$assigned->id]);
        $this->get(route('calendar.index', ['student_id' => $other->student_id]))->assertSessionHasErrors('student_id');
        $this->get(route('patients.show', $other->patient))->assertForbidden();
        $this->get(route('encounters.show', $shared))->assertForbidden();
    }

    public function test_professor_can_review_assigned_students_but_cannot_access_other_records(): void
    {
        Storage::fake('clinical');
        $professor = User::factory()->professor()->create();
        $assigned = PatientEncounter::factory()->for(User::factory()->create(['professor_id' => $professor->id]), 'student')->create();
        $other = PatientEncounter::factory()->create();
        $image = EncounterImage::factory()->for($assigned, 'encounter')->create();
        Storage::disk('clinical')->put($image->file_path, 'test image');
        $hiddenImage = EncounterImage::factory()->for($other, 'encounter')->create();
        $hiddenAi = AiReview::factory()->for($other, 'encounter')->create();
        $hiddenFeedback = HodReview::factory()->for($other, 'encounter')->create();
        $profile = StudentProfile::factory()->for($assigned->student)->create(['avatar_path' => 'avatars/assigned.png']);
        Storage::disk('clinical')->put($profile->avatar_path, 'test avatar');

        $this->actingAs($professor)->get(route('encounters.show', $assigned))->assertSee('Add faculty feedback');
        $this->get(route('encounter-images.show', $image))->assertOk();
        $this->get(route('students.avatar', $assigned->student))->assertOk();
        $this->post(route('professor.reviews.store', $assigned), ['comment' => 'Clarify the symptom timeline.'])->assertSessionHasNoErrors();
        $review = $assigned->hodReviews()->firstOrFail();
        $this->assertSame($professor->id, $review->hod_id);
        $this->assertNotNull($assigned->fresh()->locked_at);
        $this->patch(route('hod-reviews.update', $review), ['comment' => 'Clarify the updated timeline.'])->assertSessionHasNoErrors();
        $this->assertSame('Clarify the updated timeline.', $review->fresh()->comment);
        $this->get(route('encounters.show', $other))->assertForbidden();
        $this->get(route('students.avatar', $other->student))->assertForbidden();
        $this->get(route('encounter-images.show', $hiddenImage))->assertForbidden();
        $this->get(route('ai-reviews.show', $hiddenAi))->assertForbidden();
        $this->get(route('hod-reviews.show', $hiddenFeedback))->assertForbidden();
        $this->post(route('professor.reviews.store', $other), ['comment' => 'Unauthorized feedback'])->assertForbidden();
        $this->assertDatabaseMissing('hod_reviews', ['comment' => 'Unauthorized feedback']);
        $this->assertNull($other->fresh()->locked_at);
        $this->patch(route('encounters.update', $assigned), ['summary' => 'Unauthorized edit'])->assertForbidden();
        $assigned->student->forceFill(['professor_id' => null])->save();
        $this->get(route('hod-reviews.show', $review))->assertForbidden();
        $this->patch(route('hod-reviews.update', $review), ['comment' => 'No longer assigned'])->assertForbidden();
        $this->assertSame('Clarify the updated timeline.', $review->fresh()->comment);
    }

    public function test_empty_professor_workspace_is_private_and_hod_keeps_full_access(): void
    {
        $professor = User::factory()->professor()->create();
        $encounter = PatientEncounter::factory()->create();
        $this->actingAs($professor)->get(route('dashboard'))->assertViewHas('recent', fn ($rows) => $rows->isEmpty());
        $this->get(route('professor.students.index'))->assertViewHas('students', fn ($rows) => $rows->isEmpty());
        $this->get(route('patients.index'))->assertViewHas('patients', fn ($rows) => $rows->isEmpty());
        $this->get(route('patients.create'))->assertForbidden();
        $this->actingAs(User::factory()->hod()->create())->get(route('hod.students.show', $encounter->student))->assertSee($encounter->student->name);
        $this->get(route('encounters.show', $encounter))->assertSee('Add faculty feedback');
        $this->post(route('hod.reviews.store', $encounter), ['comment' => 'HOD department feedback'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('hod_reviews', ['encounter_id' => $encounter->id, 'comment' => 'HOD department feedback']);
    }

    public function test_professor_login_requires_password_change_and_inactive_account_is_blocked(): void
    {
        $professor = User::factory()->professor()->temporaryPassword()->create();
        $this->actingAs($professor)->get(route('dashboard'))->assertRedirectToRoute('password.edit');
        $professor->forceFill(['is_active' => false])->save();
        $this->get(route('dashboard'))->assertRedirectToRoute('login');
    }

    public function test_professor_creation_rejects_privilege_injection_and_guest_access(): void
    {
        $this->get(route('hod.professors.create'))->assertRedirectToRoute('login');
        $this->actingAs(User::factory()->hod()->create())->post(route('hod.professors.store'), ['name' => 'Injected role', 'email' => 'injected@example.test', 'password' => 'TemporaryPass123!', 'role' => 'hod'])->assertSessionHasErrors('role');
        $this->assertDatabaseMissing('users', ['email' => 'injected@example.test']);
        $hod = User::factory()->hod()->create();
        $this->patch(route('hod.professors.status.update', $hod), ['is_active' => false])->assertNotFound();
        $this->assertTrue($hod->fresh()->is_active);
    }

    public function test_editing_student_preserves_inactive_professor_assignment(): void
    {
        $professor = User::factory()->professor()->inactive()->create();
        $profile = StudentProfile::factory()->for(User::factory()->create(['professor_id' => $professor->id]))->create();
        $student = $profile->user;

        $this->actingAs(User::factory()->hod()->create())->get(route('hod.students.edit', $student))->assertSee($professor->name)->assertSee('(Inactive)');
        $this->patch(route('hod.students.update', $student), ['name' => $student->name, 'email' => $student->email, 'roll_number' => $profile->roll_number, 'batch' => $profile->batch, 'academic_year' => $profile->academic_year, 'professor_id' => $professor->id])->assertSessionHasNoErrors();
        $this->assertSame($professor->id, $student->fresh()->professor_id);
    }
}
