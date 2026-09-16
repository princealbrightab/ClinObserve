<?php

namespace Tests\Feature;

use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StudentManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_hod_creates_student_and_academic_profile(): void
    {
        $hod = User::factory()->hod()->create();
        $this->actingAs($hod)->post(route('hod.students.store'), ['name' => 'Academic Student', 'email' => 'academic@example.test', 'password' => 'TemporaryPass123!', 'roll_number' => 'MED-001', 'batch' => 'A', 'academic_year' => '2026–2027'])->assertSessionHasNoErrors()->assertRedirect();
        $student = User::where('email', 'academic@example.test')->firstOrFail();
        $this->assertTrue($student->isStudent());
        $this->assertTrue($student->must_change_password);
        $this->assertTrue(Hash::check('TemporaryPass123!', $student->password));
        $this->assertDatabaseHas('student_profiles', ['user_id' => $student->id, 'roll_number' => 'MED-001']);
        $this->get(route('hod.students.show', $student))->assertSee('Academic Student');
    }

    public function test_student_can_update_own_permitted_fields(): void
    {
        $profile = StudentProfile::factory()->create();
        $this->actingAs($profile->user)->patch(route('profile.update'), ['phone' => '12345', 'bio' => 'Learning careful documentation.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('student_profiles', ['id' => $profile->id, 'phone' => '12345', 'bio' => 'Learning careful documentation.']);
    }

    public function test_student_cannot_escalate_role_or_edit_another_profile(): void
    {
        $profile = StudentProfile::factory()->create();
        $other = StudentProfile::factory()->create();
        $this->actingAs($profile->user)->patch(route('profile.update'), ['role' => 'hod', 'bio' => 'Injected'])->assertSessionHasErrors('role');
        $this->assertTrue($profile->user->fresh()->isStudent());
        $this->assertNull($profile->fresh()->bio);
        $this->patch(route('hod.students.update', $other->user), ['name' => 'Changed'])->assertForbidden();
        $this->get(route('hod.students.show', $other->user))->assertForbidden();
    }

    public function test_hod_cannot_change_another_hod_through_student_routes(): void
    {
        $hod = User::factory()->hod()->create();
        $other = User::factory()->hod()->create();
        $this->actingAs($hod)->patch(route('hod.students.status.update', $other), ['is_active' => false])->assertForbidden();
        $this->assertTrue($other->fresh()->is_active);
    }

    public function test_avatar_is_private_and_reencoded(): void
    {
        Storage::fake('clinical');
        $profile = StudentProfile::factory()->create();
        $other = User::factory()->create();
        $this->actingAs($profile->user)->patch(route('profile.update'), ['avatar' => UploadedFile::fake()->image('photo.jpg')])->assertSessionHasNoErrors();
        $path = $profile->fresh()->avatar_path;
        Storage::disk('clinical')->assertExists($path);
        $this->get(route('students.avatar', $profile->user))->assertOk()->assertHeader('content-type', 'image/png');
        $this->actingAs($other)->get(route('students.avatar', $profile->user))->assertForbidden();
    }

    public function test_hod_can_reset_credentials_and_deactivate_student(): void
    {
        $hod = User::factory()->hod()->create();
        $student = User::factory()->create();
        $this->actingAs($hod)->put(route('hod.students.credentials.update', $student), ['password' => 'TemporaryReset123!', 'password_confirmation' => 'TemporaryReset123!'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('TemporaryReset123!', $student->fresh()->password));
        $this->assertTrue($student->fresh()->must_change_password);
        $this->patch(route('hod.students.status.update', $student), ['is_active' => false])->assertSessionHasNoErrors();
        $this->assertFalse($student->fresh()->is_active);
    }
}
