<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Catalog;
use Cartino\Models\User;

class CatalogPolicy
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
     * Determine if the user can view any catalogs.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view catalogs');
    }

    /**
     * Determine if the user can view the catalog.
     */
    public function view(User $user, Catalog $catalog): bool
    {
        return $user->can('view catalogs');
    }

    /**
     * Determine if the user can create catalogs.
     */
    public function create(User $user): bool
    {
        return $user->can('create catalogs');
    }

    /**
     * Determine if the user can update the catalog.
     */
    public function update(User $user, Catalog $catalog): bool
    {
        return $user->can('edit catalogs');
    }

    /**
     * Determine if the user can delete the catalog.
     */
    public function delete(User $user, Catalog $catalog): bool
    {
        return $user->can('delete catalogs');
    }

    /**
     * Determine if the user can restore the catalog.
     */
    public function restore(User $user, Catalog $catalog): bool
    {
        return $user->can('delete catalogs');
    }

    /**
     * Determine if the user can permanently delete the catalog.
     */
    public function forceDelete(User $user, Catalog $catalog): bool
    {
        return $user->can('delete catalogs');
    }
}
