<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class ReviewPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('reviews.view') || $user->can('reviews.manage');
    }

    public function view(User $user, Review $review): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('reviews.manage');
    }

    public function update(User $user, Review $review): bool
    {
        return $user->can('reviews.manage');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->can('reviews.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('reviews.manage');
    }
}
