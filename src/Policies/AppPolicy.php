<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\App;
use Cartino\Models\User;

class AppPolicy
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
     * Determine if the user can view any apps.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view apps');
    }

    /**
     * Determine if the user can view the app.
     */
    public function view(User $user, App $app): bool
    {
        return $user->can('view apps');
    }

    /**
     * Determine if the user can create apps.
     */
    public function create(User $user): bool
    {
        return $user->can('create apps');
    }

    /**
     * Determine if the user can update the app.
     */
    public function update(User $user, App $app): bool
    {
        return $user->can('edit apps');
    }

    /**
     * Determine if the user can delete the app.
     */
    public function delete(User $user, App $app): bool
    {
        return $user->can('delete apps');
    }

    /**
     * Determine if the user can restore the app.
     */
    public function restore(User $user, App $app): bool
    {
        return $user->can('delete apps');
    }

    /**
     * Determine if the user can permanently delete the app.
     */
    public function forceDelete(User $user, App $app): bool
    {
        return $user->can('delete apps');
    }
}
