<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;
use Cartino\Models\UserPreference;

class UserPreferencePolicy
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
     * Determine if the user can view any user_preferences.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view user_preferences');
    }

    /**
     * Determine if the user can view the user_preference.
     */
    public function view(User $user, UserPreference $user_preference): bool
    {
        return $user->can('view user_preferences');
    }

    /**
     * Determine if the user can create user_preferences.
     */
    public function create(User $user): bool
    {
        return $user->can('create user_preferences');
    }

    /**
     * Determine if the user can update the user_preference.
     */
    public function update(User $user, UserPreference $user_preference): bool
    {
        return $user->can('edit user_preferences');
    }

    /**
     * Determine if the user can delete the user_preference.
     */
    public function delete(User $user, UserPreference $user_preference): bool
    {
        return $user->can('delete user_preferences');
    }

    /**
     * Determine if the user can restore the user_preference.
     */
    public function restore(User $user, UserPreference $user_preference): bool
    {
        return $user->can('delete user_preferences');
    }

    /**
     * Determine if the user can permanently delete the user_preference.
     */
    public function forceDelete(User $user, UserPreference $user_preference): bool
    {
        return $user->can('delete user_preferences');
    }
}
