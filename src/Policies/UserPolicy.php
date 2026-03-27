<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;

class UserPolicy
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
     * Determine if the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Determine if the user can view the user.
     */
    public function view(User $user, User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Determine if the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determine if the user can update the user.
     */
    public function update(User $user, User $user): bool
    {
        return $user->can('edit users');
    }

    /**
     * Determine if the user can delete the user.
     */
    public function delete(User $user, User $user): bool
    {
        return $user->can('delete users');
    }

    /**
     * Determine if the user can restore the user.
     */
    public function restore(User $user, User $user): bool
    {
        return $user->can('delete users');
    }

    /**
     * Determine if the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $user): bool
    {
        return $user->can('delete users');
    }
}
