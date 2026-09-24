<?php

namespace App\Policies;

use App\Models\UnansweredQuestion;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class UnansweredQuestionPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, UnansweredQuestion $unansweredQuestion): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, UnansweredQuestion $unansweredQuestion): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, UnansweredQuestion $unansweredQuestion): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
