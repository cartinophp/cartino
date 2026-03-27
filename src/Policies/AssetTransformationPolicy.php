<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AssetTransformation;
use Cartino\Models\User;

class AssetTransformationPolicy
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
     * Determine if the user can view any asset_transformations.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view asset_transformations');
    }

    /**
     * Determine if the user can view the asset_transformation.
     */
    public function view(User $user, AssetTransformation $asset_transformation): bool
    {
        return $user->can('view asset_transformations');
    }

    /**
     * Determine if the user can create asset_transformations.
     */
    public function create(User $user): bool
    {
        return $user->can('create asset_transformations');
    }

    /**
     * Determine if the user can update the asset_transformation.
     */
    public function update(User $user, AssetTransformation $asset_transformation): bool
    {
        return $user->can('edit asset_transformations');
    }

    /**
     * Determine if the user can delete the asset_transformation.
     */
    public function delete(User $user, AssetTransformation $asset_transformation): bool
    {
        return $user->can('delete asset_transformations');
    }

    /**
     * Determine if the user can restore the asset_transformation.
     */
    public function restore(User $user, AssetTransformation $asset_transformation): bool
    {
        return $user->can('delete asset_transformations');
    }

    /**
     * Determine if the user can permanently delete the asset_transformation.
     */
    public function forceDelete(User $user, AssetTransformation $asset_transformation): bool
    {
        return $user->can('delete asset_transformations');
    }
}
