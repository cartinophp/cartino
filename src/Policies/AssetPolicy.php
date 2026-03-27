<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Asset;
use Cartino\Models\User;

class AssetPolicy
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
     * Determine if the user can view any assets.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view assets');
    }

    /**
     * Determine if the user can view the asset.
     */
    public function view(User $user, Asset $asset): bool
    {
        return $user->can('view assets');
    }

    /**
     * Determine if the user can create assets.
     */
    public function create(User $user): bool
    {
        return $user->can('create assets');
    }

    /**
     * Determine if the user can update the asset.
     */
    public function update(User $user, Asset $asset): bool
    {
        return $user->can('edit assets');
    }

    /**
     * Determine if the user can delete the asset.
     */
    public function delete(User $user, Asset $asset): bool
    {
        return $user->can('delete assets');
    }

    /**
     * Determine if the user can restore the asset.
     */
    public function restore(User $user, Asset $asset): bool
    {
        return $user->can('delete assets');
    }

    /**
     * Determine if the user can permanently delete the asset.
     */
    public function forceDelete(User $user, Asset $asset): bool
    {
        return $user->can('delete assets');
    }
}
