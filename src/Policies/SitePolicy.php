<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Site;
use Cartino\Models\User;

class SitePolicy
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
     * Determine if the user can view any sites.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view sites');
    }

    /**
     * Determine if the user can view the site.
     */
    public function view(User $user, Site $site): bool
    {
        return $user->can('view sites');
    }

    /**
     * Determine if the user can create sites.
     */
    public function create(User $user): bool
    {
        return $user->can('create sites');
    }

    /**
     * Determine if the user can update the site.
     */
    public function update(User $user, Site $site): bool
    {
        return $user->can('edit sites');
    }

    /**
     * Determine if the user can delete the site.
     */
    public function delete(User $user, Site $site): bool
    {
        return $user->can('delete sites');
    }

    /**
     * Determine if the user can restore the site.
     */
    public function restore(User $user, Site $site): bool
    {
        return $user->can('delete sites');
    }

    /**
     * Determine if the user can permanently delete the site.
     */
    public function forceDelete(User $user, Site $site): bool
    {
        return $user->can('delete sites');
    }
}
