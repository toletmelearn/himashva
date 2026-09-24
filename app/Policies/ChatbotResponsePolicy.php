<?php

namespace App\Policies;

use App\Models\ChatbotResponse;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class ChatbotResponsePolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, ChatbotResponse $chatbotResponse): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, ChatbotResponse $chatbotResponse): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, ChatbotResponse $chatbotResponse): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
