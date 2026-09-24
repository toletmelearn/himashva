<?php

namespace App\Policies;

use App\Models\NewsletterSubscriber;
use App\Models\User;
use App\Policies\Concerns\BypassesForLegacyAdmin;

class NewsletterSubscriberPolicy
{
    use BypassesForLegacyAdmin;

    public function viewAny(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function view(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->can('cms.manage');
    }

    public function update(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('cms.manage');
    }

    public function delete(User $user, NewsletterSubscriber $newsletterSubscriber): bool
    {
        return $user->can('cms.manage');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('cms.manage');
    }
}
