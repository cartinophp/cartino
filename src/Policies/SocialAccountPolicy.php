<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\SocialAccount;
use Cartino\Models\User;

class SocialAccountPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any social_accounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view social_accounts');
    }

    /**
     * Determine if the user can view the social_account.
     */
    public function view(User $user, SocialAccount $social_account): bool
    {
        return $user->can('view social_accounts');
    }

    /**
     * Determine if the user can create social_accounts.
     */
    public function create(User $user): bool
    {
        return $user->can('create social_accounts');
    }

    /**
     * Determine if the user can update the social_account.
     */
    public function update(User $user, SocialAccount $social_account): bool
    {
        return $user->can('edit social_accounts');
    }

    /**
     * Determine if the user can delete the social_account.
     */
    public function delete(User $user, SocialAccount $social_account): bool
    {
        return $user->can('delete social_accounts');
    }

    /**
     * Determine if the user can restore the social_account.
     */
    public function restore(User $user, SocialAccount $social_account): bool
    {
        return $user->can('delete social_accounts');
    }

    /**
     * Determine if the user can permanently delete the social_account.
     */
    public function forceDelete(User $user, SocialAccount $social_account): bool
    {
        return $user->can('delete social_accounts');
    }
}
