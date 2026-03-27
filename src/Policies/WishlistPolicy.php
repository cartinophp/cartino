<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;
use Cartino\Models\Wishlist;

class WishlistPolicy
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
     * Determine if the user can view any wishlists.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view wishlists');
    }

    /**
     * Determine if the user can view the wishlist.
     */
    public function view(User $user, Wishlist $wishlist): bool
    {
        return $user->can('view wishlists');
    }

    /**
     * Determine if the user can create wishlists.
     */
    public function create(User $user): bool
    {
        return $user->can('create wishlists');
    }

    /**
     * Determine if the user can update the wishlist.
     */
    public function update(User $user, Wishlist $wishlist): bool
    {
        return $user->can('edit wishlists');
    }

    /**
     * Determine if the user can delete the wishlist.
     */
    public function delete(User $user, Wishlist $wishlist): bool
    {
        return $user->can('delete wishlists');
    }

    /**
     * Determine if the user can restore the wishlist.
     */
    public function restore(User $user, Wishlist $wishlist): bool
    {
        return $user->can('delete wishlists');
    }

    /**
     * Determine if the user can permanently delete the wishlist.
     */
    public function forceDelete(User $user, Wishlist $wishlist): bool
    {
        return $user->can('delete wishlists');
    }
}
