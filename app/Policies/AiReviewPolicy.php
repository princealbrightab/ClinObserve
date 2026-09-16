<?php

namespace App\Policies;

use App\Models\AiReview;
use App\Models\User;

class AiReviewPolicy
{
    public function view(User $user, AiReview $review): bool
    {
        return $user->can('privateFeedback', $review->encounter);
    }
}
