<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ShippingRate;
use Cartino\Models\User;

class ShippingRatePolicy
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
     * Determine if the user can view any shipping_rates.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view shipping_rates');
    }

    /**
     * Determine if the user can view the shipping_rate.
     */
    public function view(User $user, ShippingRate $shipping_rate): bool
    {
        return $user->can('view shipping_rates');
    }

    /**
     * Determine if the user can create shipping_rates.
     */
    public function create(User $user): bool
    {
        return $user->can('create shipping_rates');
    }

    /**
     * Determine if the user can update the shipping_rate.
     */
    public function update(User $user, ShippingRate $shipping_rate): bool
    {
        return $user->can('edit shipping_rates');
    }

    /**
     * Determine if the user can delete the shipping_rate.
     */
    public function delete(User $user, ShippingRate $shipping_rate): bool
    {
        return $user->can('delete shipping_rates');
    }

    /**
     * Determine if the user can restore the shipping_rate.
     */
    public function restore(User $user, ShippingRate $shipping_rate): bool
    {
        return $user->can('delete shipping_rates');
    }

    /**
     * Determine if the user can permanently delete the shipping_rate.
     */
    public function forceDelete(User $user, ShippingRate $shipping_rate): bool
    {
        return $user->can('delete shipping_rates');
    }
}
