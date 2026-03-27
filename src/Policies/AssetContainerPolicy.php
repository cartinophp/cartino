<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AssetContainer;
use Cartino\Models\User;

class AssetContainerPolicy
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
     * Determine if the user can view any asset_containers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view asset_containers');
    }

    /**
     * Determine if the user can view the asset_container.
     */
    public function view(User $user, AssetContainer $asset_container): bool
    {
        return $user->can('view asset_containers');
    }

    /**
     * Determine if the user can create asset_containers.
     */
    public function create(User $user): bool
    {
        return $user->can('create asset_containers');
    }

    /**
     * Determine if the user can update the asset_container.
     */
    public function update(User $user, AssetContainer $asset_container): bool
    {
        return $user->can('edit asset_containers');
    }

    /**
     * Determine if the user can delete the asset_container.
     */
    public function delete(User $user, AssetContainer $asset_container): bool
    {
        return $user->can('delete asset_containers');
    }

    /**
     * Determine if the user can restore the asset_container.
     */
    public function restore(User $user, AssetContainer $asset_container): bool
    {
        return $user->can('delete asset_containers');
    }

    /**
     * Determine if the user can permanently delete the asset_container.
     */
    public function forceDelete(User $user, AssetContainer $asset_container): bool
    {
        return $user->can('delete asset_containers');
    }
}
