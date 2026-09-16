<?php

namespace App\Policies;

use App\Models\PatientEncounter;
use App\Models\User;

class PatientEncounterPolicy
{
    public function view(User $user, PatientEncounter $encounter): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->isStudent();
    }

    public function update(User $user, PatientEncounter $encounter): bool
    {
        return $user->isStudent() && $user->id === $encounter->student_id && $encounter->locked_at === null;
    }

    public function privateFeedback(User $user, PatientEncounter $encounter): bool
    {
        return $user->isHod() || $user->id === $encounter->student_id;
    }

    public function requestAi(User $user, PatientEncounter $encounter): bool
    {
        return $user->isStudent() && $user->id === $encounter->student_id;
    }
}
