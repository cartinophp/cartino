<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Favorite;
use Cartino\Models\User;

class FavoritePolicy
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
     * Determine if the user can view any favorites.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view favorites');
    }

    /**
     * Determine if the user can view the favorite.
     */
    public function view(User $user, Favorite $favorite): bool
    {
        return $user->can('view favorites');
    }

    /**
     * Determine if the user can create favorites.
     */
    public function create(User $user): bool
    {
        return $user->can('create favorites');
    }

    /**
     * Determine if the user can update the favorite.
     */
    public function update(User $user, Favorite $favorite): bool
    {
        return $user->can('edit favorites');
    }

    /**
     * Determine if the user can delete the favorite.
     */
    public function delete(User $user, Favorite $favorite): bool
    {
        return $user->can('delete favorites');
    }

    /**
     * Determine if the user can restore the favorite.
     */
    public function restore(User $user, Favorite $favorite): bool
    {
        return $user->can('delete favorites');
    }

    /**
     * Determine if the user can permanently delete the favorite.
     */
    public function forceDelete(User $user, Favorite $favorite): bool
    {
        return $user->can('delete favorites');
    }
}
