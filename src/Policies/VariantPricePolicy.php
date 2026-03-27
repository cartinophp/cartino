<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\User;
use Cartino\Models\VariantPrice;

class VariantPricePolicy
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
     * Determine if the user can view any variant_prices.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view variant_prices');
    }

    /**
     * Determine if the user can view the variant_price.
     */
    public function view(User $user, VariantPrice $variant_price): bool
    {
        return $user->can('view variant_prices');
    }

    /**
     * Determine if the user can create variant_prices.
     */
    public function create(User $user): bool
    {
        return $user->can('create variant_prices');
    }

    /**
     * Determine if the user can update the variant_price.
     */
    public function update(User $user, VariantPrice $variant_price): bool
    {
        return $user->can('edit variant_prices');
    }

    /**
     * Determine if the user can delete the variant_price.
     */
    public function delete(User $user, VariantPrice $variant_price): bool
    {
        return $user->can('delete variant_prices');
    }

    /**
     * Determine if the user can restore the variant_price.
     */
    public function restore(User $user, VariantPrice $variant_price): bool
    {
        return $user->can('delete variant_prices');
    }

    /**
     * Determine if the user can permanently delete the variant_price.
     */
    public function forceDelete(User $user, VariantPrice $variant_price): bool
    {
        return $user->can('delete variant_prices');
    }
}
