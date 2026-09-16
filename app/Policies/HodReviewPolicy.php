<?php

namespace App\Policies;

use App\Models\HodReview;
use App\Models\User;

class HodReviewPolicy
{
    public function view(User $user, HodReview $review): bool
    {
        return $user->can('privateFeedback', $review->encounter);
    }

    public function update(User $user, HodReview $review): bool
    {
        return $user->isHod() && $user->id === $review->hod_id;
    }
}
