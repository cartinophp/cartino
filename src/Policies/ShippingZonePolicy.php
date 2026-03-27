<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ShippingZone;
use Cartino\Models\User;

class ShippingZonePolicy
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
     * Determine if the user can view any shipping_zones.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view shipping_zones');
    }

    /**
     * Determine if the user can view the shipping_zone.
     */
    public function view(User $user, ShippingZone $shipping_zone): bool
    {
        return $user->can('view shipping_zones');
    }

    /**
     * Determine if the user can create shipping_zones.
     */
    public function create(User $user): bool
    {
        return $user->can('create shipping_zones');
    }

    /**
     * Determine if the user can update the shipping_zone.
     */
    public function update(User $user, ShippingZone $shipping_zone): bool
    {
        return $user->can('edit shipping_zones');
    }

    /**
     * Determine if the user can delete the shipping_zone.
     */
    public function delete(User $user, ShippingZone $shipping_zone): bool
    {
        return $user->can('delete shipping_zones');
    }

    /**
     * Determine if the user can restore the shipping_zone.
     */
    public function restore(User $user, ShippingZone $shipping_zone): bool
    {
        return $user->can('delete shipping_zones');
    }

    /**
     * Determine if the user can permanently delete the shipping_zone.
     */
    public function forceDelete(User $user, ShippingZone $shipping_zone): bool
    {
        return $user->can('delete shipping_zones');
    }
}
