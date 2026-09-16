<?php

namespace App\Services;

use App\Models\AiReview;
use App\Models\HodReview;
use App\Models\Patient;
use App\Models\PatientEncounter;
use App\Models\User;
use Carbon\CarbonImmutable;

class ReportService
{
    public function dashboard(User $user): array
    {
        $tz = config('clinobserve.timezone');
        $now = CarbonImmutable::now($tz);
        $query = PatientEncounter::query()->when(! $user->isHod(), fn ($q) => $q->where('student_id', $user->id));
        $stats = ['Patients observed' => (clone $query)->distinct()->count('patient_id'), 'Encounters' => (clone $query)->count(), 'This month' => (clone $query)->whereBetween('attended_at', [$now->startOfMonth()->utc(), $now->endOfMonth()->utc()])->count(), 'Today' => (clone $query)->whereBetween('attended_at', [$now->startOfDay()->utc(), $now->endOfDay()->utc()])->count()];
        if ($user->isHod()) {
            $stats = ['Students' => User::where('role', 'student')->count(), 'Cases' => Patient::count()] + $stats;
        }

        return ['stats' => $stats, 'recent' => (clone $query)->with(['patient', 'student'])->latest('attended_at')->limit(6)->get(),
            'feedback' => HodReview::with(['encounter.patient', 'hod'])->when(! $user->isHod(), fn ($q) => $q->whereHas('encounter', fn ($q) => $q->where('student_id', $user->id)))->latest()->limit(4)->get(),
            'aiReviews' => AiReview::with('encounter.patient')->when(! $user->isHod(), fn ($q) => $q->where('requested_by', $user->id))->latest()->limit(4)->get(),
            'pending' => (clone $query)->doesntHave('hodReviews')->count(),
            'activeStudents' => $user->isHod() ? User::where('role', 'student')->whereHas('encounters', fn ($q) => $q->where('attended_at', '>=', now()->subDays(30)))->withCount('encounters')->limit(6)->get() : collect()];
    }
}
