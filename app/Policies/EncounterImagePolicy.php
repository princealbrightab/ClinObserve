<?php

namespace App\Policies;

use App\Models\EncounterImage;
use App\Models\User;

class EncounterImagePolicy
{
    public function view(User $user, EncounterImage $image): bool
    {
        return $user->can('view', $image->encounter);
    }

    public function delete(User $user, EncounterImage $image): bool
    {
        return $user->can('update', $image->encounter);
    }
}
