<?php

namespace App\Services;

use App\Models\AiReview;
use App\Models\HodReview;
use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use App\UserRole;
use Carbon\CarbonImmutable;

class ReportService
{
    public function dashboard(User $user): array
    {
        $tz = config('clinobserve.timezone');
        $now = CarbonImmutable::now($tz);
        $query = PatientEncounter::query()->forWorkspace($user);
        $stats = ['Patients observed' => (clone $query)->distinct()->count('patient_id'), 'Encounters' => (clone $query)->count(), 'This month' => (clone $query)->whereBetween('attended_at', [$now->startOfMonth()->utc(), $now->endOfMonth()->utc()])->count(), 'Today' => (clone $query)->whereBetween('attended_at', [$now->startOfDay()->utc(), $now->endOfDay()->utc()])->count()];

        $unassignedStudents = collect();
        $professorsWithoutStudents = collect();

        if ($user->isHod()) {
            $students = User::where('role', UserRole::Student)->with(['studentProfile', 'professor'])->get();
            $professors = User::where('role', UserRole::Professor)->withCount('assignedStudents')->get();
            $unassignedStudents = $students->filter(fn (User $student) => is_null($student->professor_id))->values();
            $professorsWithoutStudents = $professors->filter(fn (User $professor) => (int) $professor->assigned_students_count === 0)->values();

            $stats = ['Students' => $students->count(), 'Professors' => $professors->count(), 'Unassigned students' => $unassignedStudents->count(), 'Professors without students' => $professorsWithoutStudents->count(), 'Cases' => Patient::count()] + $stats;
        }

        return ['stats' => $stats, 'recent' => (clone $query)->with(['patient', 'student'])->latest('attended_at')->limit(6)->get(),
            'feedback' => HodReview::with(['encounter.patient', 'hod'])->whereHas('encounter', fn ($q) => $q->forWorkspace($user))->latest()->limit(4)->get(),
            'aiReviews' => AiReview::with('encounter.patient')->whereHas('encounter', fn ($q) => $q->visibleToProfessor($user))->when($user->isStudent(), fn ($q) => $q->where('requested_by', $user->id))->latest()->limit(4)->get(),
            'pending' => (clone $query)->doesntHave('hodReviews')->count(),
            'activeStudents' => $user->isHod() ? User::where('role', 'student')->whereHas('encounters', fn ($q) => $q->where('attended_at', '>=', now()->subDays(30)))->withCount('encounters')->limit(6)->get() : collect(),
            'unassignedStudents' => $unassignedStudents,
            'professorsWithoutStudents' => $professorsWithoutStudents];
    }
}
