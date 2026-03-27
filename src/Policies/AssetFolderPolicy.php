<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AssetFolder;
use Cartino\Models\User;

class AssetFolderPolicy
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
     * Determine if the user can view any asset_folders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view asset_folders');
    }

    /**
     * Determine if the user can view the asset_folder.
     */
    public function view(User $user, AssetFolder $asset_folder): bool
    {
        return $user->can('view asset_folders');
    }

    /**
     * Determine if the user can create asset_folders.
     */
    public function create(User $user): bool
    {
        return $user->can('create asset_folders');
    }

    /**
     * Determine if the user can update the asset_folder.
     */
    public function update(User $user, AssetFolder $asset_folder): bool
    {
        return $user->can('edit asset_folders');
    }

    /**
     * Determine if the user can delete the asset_folder.
     */
    public function delete(User $user, AssetFolder $asset_folder): bool
    {
        return $user->can('delete asset_folders');
    }

    /**
     * Determine if the user can restore the asset_folder.
     */
    public function restore(User $user, AssetFolder $asset_folder): bool
    {
        return $user->can('delete asset_folders');
    }

    /**
     * Determine if the user can permanently delete the asset_folder.
     */
    public function forceDelete(User $user, AssetFolder $asset_folder): bool
    {
        return $user->can('delete asset_folders');
    }
}
