<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;

class GlobalPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_super ?? false) {
            return true;
        }

        return null;
    }

    /**
     * Determine whether the user can view any globals.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view globals');
    }

    /**
     * Determine whether the user can view the global.
     */
    public function view(User $user, GlobalModel $global): bool
    {
        return $user->can('view globals');
    }

    /**
     * Determine whether the user can create globals.
     */
    public function create(User $user): bool
    {
        return $user->can('create globals');
    }

    /**
     * Determine whether the user can update the global.
     */
    public function update(User $user, GlobalModel $global): bool
    {
        return $user->can('edit globals');
    }

    /**
     * Determine whether the user can delete the global.
     */
    public function delete(User $user, GlobalModel $global): bool
    {
        return $user->can('delete globals');
    }

    /**
     * Determine whether the user can restore the global.
     */
    public function restore(User $user, GlobalModel $global): bool
    {
        return $user->can('restore globals');
    }

    /**
     * Determine whether the user can permanently delete the global.
     */
    public function forceDelete(User $user, GlobalModel $global): bool
    {
        return $user->can('force delete globals');
    }
}
