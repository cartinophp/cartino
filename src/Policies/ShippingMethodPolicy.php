<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ShippingMethod;
use Cartino\Models\User;

class ShippingMethodPolicy
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
     * Determine if the user can view any shipping_methods.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view shipping_methods');
    }

    /**
     * Determine if the user can view the shipping_method.
     */
    public function view(User $user, ShippingMethod $shipping_method): bool
    {
        return $user->can('view shipping_methods');
    }

    /**
     * Determine if the user can create shipping_methods.
     */
    public function create(User $user): bool
    {
        return $user->can('create shipping_methods');
    }

    /**
     * Determine if the user can update the shipping_method.
     */
    public function update(User $user, ShippingMethod $shipping_method): bool
    {
        return $user->can('edit shipping_methods');
    }

    /**
     * Determine if the user can delete the shipping_method.
     */
    public function delete(User $user, ShippingMethod $shipping_method): bool
    {
        return $user->can('delete shipping_methods');
    }

    /**
     * Determine if the user can restore the shipping_method.
     */
    public function restore(User $user, ShippingMethod $shipping_method): bool
    {
        return $user->can('delete shipping_methods');
    }

    /**
     * Determine if the user can permanently delete the shipping_method.
     */
    public function forceDelete(User $user, ShippingMethod $shipping_method): bool
    {
        return $user->can('delete shipping_methods');
    }
}
