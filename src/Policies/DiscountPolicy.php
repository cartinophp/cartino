<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Discount;
use Cartino\Models\User;

class DiscountPolicy
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
     * Determine if the user can view any discounts.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view discounts');
    }

    /**
     * Determine if the user can view the discount.
     */
    public function view(User $user, Discount $discount): bool
    {
        return $user->can('view discounts');
    }

    /**
     * Determine if the user can create discounts.
     */
    public function create(User $user): bool
    {
        return $user->can('create discounts');
    }

    /**
     * Determine if the user can update the discount.
     */
    public function update(User $user, Discount $discount): bool
    {
        return $user->can('edit discounts');
    }

    /**
     * Determine if the user can delete the discount.
     */
    public function delete(User $user, Discount $discount): bool
    {
        return $user->can('delete discounts');
    }

    /**
     * Determine if the user can restore the discount.
     */
    public function restore(User $user, Discount $discount): bool
    {
        return $user->can('delete discounts');
    }

    /**
     * Determine if the user can permanently delete the discount.
     */
    public function forceDelete(User $user, Discount $discount): bool
    {
        return $user->can('delete discounts');
    }
}
