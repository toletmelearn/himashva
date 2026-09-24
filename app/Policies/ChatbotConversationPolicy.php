<?php

namespace App\Policies;

use App\Models\ChatbotConversation;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class ChatbotConversationPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, ChatbotConversation $chatbotConversation): bool
    {
        return $this->viewAny($user);
    }

    public function delete(User $user, ChatbotConversation $chatbotConversation): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
