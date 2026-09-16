<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isHod();
    }

    public function create(User $user): bool
    {
        return $user->isHod();
    }

    public function view(User $user, User $student): bool
    {
        return $user->isHod() || $user->id === $student->id;
    }

    public function update(User $user, User $student): bool
    {
        return $student->isStudent() && $user->isHod();
    }
}
