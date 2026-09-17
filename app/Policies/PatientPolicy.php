<?php

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Patient $patient): bool
    {
        return ! $user->isProfessor() || $patient->encounters()->visibleToProfessor($user)->exists();
    }

    public function create(User $user): bool
    {
        return ! $user->isProfessor();
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->isHod() || ($patient->created_by === $user->id && ! $patient->encounters()->exists());
    }
}
