<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;
use Cartino\Models\UserGroup;

class UserGroupPolicy
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
     * Determine if the user can view any user_groups.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view user_groups');
    }

    /**
     * Determine if the user can view the user_group.
     */
    public function view(User $user, UserGroup $user_group): bool
    {
        return $user->can('view user_groups');
    }

    /**
     * Determine if the user can create user_groups.
     */
    public function create(User $user): bool
    {
        return $user->can('create user_groups');
    }

    /**
     * Determine if the user can update the user_group.
     */
    public function update(User $user, UserGroup $user_group): bool
    {
        return $user->can('edit user_groups');
    }

    /**
     * Determine if the user can delete the user_group.
     */
    public function delete(User $user, UserGroup $user_group): bool
    {
        return $user->can('delete user_groups');
    }

    /**
     * Determine if the user can restore the user_group.
     */
    public function restore(User $user, UserGroup $user_group): bool
    {
        return $user->can('delete user_groups');
    }

    /**
     * Determine if the user can permanently delete the user_group.
     */
    public function forceDelete(User $user, UserGroup $user_group): bool
    {
        return $user->can('delete user_groups');
    }
}
