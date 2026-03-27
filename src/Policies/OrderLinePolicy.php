<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\OrderLine;
use Cartino\Models\User;

class OrderLinePolicy
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
     * Determine if the user can view any order_lines.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view order_lines');
    }

    /**
     * Determine if the user can view the order_line.
     */
    public function view(User $user, OrderLine $order_line): bool
    {
        return $user->can('view order_lines');
    }

    /**
     * Determine if the user can create order_lines.
     */
    public function create(User $user): bool
    {
        return $user->can('create order_lines');
    }

    /**
     * Determine if the user can update the order_line.
     */
    public function update(User $user, OrderLine $order_line): bool
    {
        return $user->can('edit order_lines');
    }

    /**
     * Determine if the user can delete the order_line.
     */
    public function delete(User $user, OrderLine $order_line): bool
    {
        return $user->can('delete order_lines');
    }

    /**
     * Determine if the user can restore the order_line.
     */
    public function restore(User $user, OrderLine $order_line): bool
    {
        return $user->can('delete order_lines');
    }

    /**
     * Determine if the user can permanently delete the order_line.
     */
    public function forceDelete(User $user, OrderLine $order_line): bool
    {
        return $user->can('delete order_lines');
    }
}
