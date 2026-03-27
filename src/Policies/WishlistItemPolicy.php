<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;
use Cartino\Models\WishlistItem;

class WishlistItemPolicy
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
     * Determine if the user can view any wishlist_items.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view wishlist_items');
    }

    /**
     * Determine if the user can view the wishlist_item.
     */
    public function view(User $user, WishlistItem $wishlist_item): bool
    {
        return $user->can('view wishlist_items');
    }

    /**
     * Determine if the user can create wishlist_items.
     */
    public function create(User $user): bool
    {
        return $user->can('create wishlist_items');
    }

    /**
     * Determine if the user can update the wishlist_item.
     */
    public function update(User $user, WishlistItem $wishlist_item): bool
    {
        return $user->can('edit wishlist_items');
    }

    /**
     * Determine if the user can delete the wishlist_item.
     */
    public function delete(User $user, WishlistItem $wishlist_item): bool
    {
        return $user->can('delete wishlist_items');
    }

    /**
     * Determine if the user can restore the wishlist_item.
     */
    public function restore(User $user, WishlistItem $wishlist_item): bool
    {
        return $user->can('delete wishlist_items');
    }

    /**
     * Determine if the user can permanently delete the wishlist_item.
     */
    public function forceDelete(User $user, WishlistItem $wishlist_item): bool
    {
        return $user->can('delete wishlist_items');
    }
}
