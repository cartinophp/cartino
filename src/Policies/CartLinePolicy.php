<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\CartLine;
use Cartino\Models\User;

class CartLinePolicy
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
     * Determine if the user can view any cart_lines.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view cart_lines');
    }

    /**
     * Determine if the user can view the cart_line.
     */
    public function view(User $user, CartLine $cart_line): bool
    {
        return $user->can('view cart_lines');
    }

    /**
     * Determine if the user can create cart_lines.
     */
    public function create(User $user): bool
    {
        return $user->can('create cart_lines');
    }

    /**
     * Determine if the user can update the cart_line.
     */
    public function update(User $user, CartLine $cart_line): bool
    {
        return $user->can('edit cart_lines');
    }

    /**
     * Determine if the user can delete the cart_line.
     */
    public function delete(User $user, CartLine $cart_line): bool
    {
        return $user->can('delete cart_lines');
    }

    /**
     * Determine if the user can restore the cart_line.
     */
    public function restore(User $user, CartLine $cart_line): bool
    {
        return $user->can('delete cart_lines');
    }

    /**
     * Determine if the user can permanently delete the cart_line.
     */
    public function forceDelete(User $user, CartLine $cart_line): bool
    {
        return $user->can('delete cart_lines');
    }
}
