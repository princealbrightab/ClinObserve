<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_login_and_logout(): void
    {
        $user = User::factory()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'StudyAccess123!'])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_invalid_credentials_are_rejected(): void
    {
        $this->post(route('login.store'), ['email' => 'missing@example.test', 'password' => 'bad'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_accounts_cannot_login_or_keep_an_existing_session(): void
    {
        $user = User::factory()->inactive()->create();
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'StudyAccess123!'])->assertSessionHasErrors('email');
        $this->actingAs($user)->get(route('patients.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_students_cannot_access_hod_pages_or_create_students(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get(route('hod.dashboard'))->assertForbidden();
        $this->get(route('hod.students.index'))->assertForbidden();
        $this->post(route('hod.students.store'), ['name' => 'Intruder'])->assertForbidden();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_temporary_password_must_be_changed_before_access(): void
    {
        $user = User::factory()->temporaryPassword()->create();
        $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.edit'));
        $this->put(route('password.update'), ['current_password' => 'StudyAccess123!', 'password' => 'NewStudyAccess456!', 'password_confirmation' => 'NewStudyAccess456!'])->assertRedirect(route('dashboard'));
        $this->assertFalse($user->fresh()->must_change_password);
        $this->assertTrue(Hash::check('NewStudyAccess456!', $user->fresh()->password));
    }

    public function test_registration_is_not_public(): void
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register')->assertNotFound();
    }

    public function test_guests_cannot_access_clinical_records(): void
    {
        $this->get(route('patients.index'))->assertRedirect(route('login'));
    }

    public function test_hod_can_open_all_management_pages(): void
    {
        $hod = User::factory()->hod()->create();
        foreach (['hod.dashboard', 'hod.students.index', 'hod.students.create', 'hod.calendar.index', 'hod.reports.index', 'hod.ai-reviews.index', 'profile.edit', 'password.edit'] as $route) {
            $this->actingAs($hod)->get(route($route))->assertOk();
        }
    }
}
