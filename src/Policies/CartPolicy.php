<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Cart;
use Cartino\Models\User;

class CartPolicy
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
     * Determine if the user can view any carts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view carts');
    }

    /**
     * Determine if the user can view the cart.
     */
    public function view(User $user, Cart $cart): bool
    {
        return $user->can('view carts');
    }

    /**
     * Determine if the user can create carts.
     */
    public function create(User $user): bool
    {
        return $user->can('create carts');
    }

    /**
     * Determine if the user can update the cart.
     */
    public function update(User $user, Cart $cart): bool
    {
        return $user->can('edit carts');
    }

    /**
     * Determine if the user can delete the cart.
     */
    public function delete(User $user, Cart $cart): bool
    {
        return $user->can('delete carts');
    }

    /**
     * Determine if the user can restore the cart.
     */
    public function restore(User $user, Cart $cart): bool
    {
        return $user->can('delete carts');
    }

    /**
     * Determine if the user can permanently delete the cart.
     */
    public function forceDelete(User $user, Cart $cart): bool
    {
        return $user->can('delete carts');
    }
}
