<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AppInstallation;
use Cartino\Models\User;

class AppInstallationPolicy
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
     * Determine if the user can view any app_installations.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view app_installations');
    }

    /**
     * Determine if the user can view the app_installation.
     */
    public function view(User $user, AppInstallation $app_installation): bool
    {
        return $user->can('view app_installations');
    }

    /**
     * Determine if the user can create app_installations.
     */
    public function create(User $user): bool
    {
        return $user->can('create app_installations');
    }

    /**
     * Determine if the user can update the app_installation.
     */
    public function update(User $user, AppInstallation $app_installation): bool
    {
        return $user->can('edit app_installations');
    }

    /**
     * Determine if the user can delete the app_installation.
     */
    public function delete(User $user, AppInstallation $app_installation): bool
    {
        return $user->can('delete app_installations');
    }

    /**
     * Determine if the user can restore the app_installation.
     */
    public function restore(User $user, AppInstallation $app_installation): bool
    {
        return $user->can('delete app_installations');
    }

    /**
     * Determine if the user can permanently delete the app_installation.
     */
    public function forceDelete(User $user, AppInstallation $app_installation): bool
    {
        return $user->can('delete app_installations');
    }
}
