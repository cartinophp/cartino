<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\DiscountApplication;
use Cartino\Models\User;

class DiscountApplicationPolicy
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
     * Determine if the user can view any discount_applications.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view discount_applications');
    }

    /**
     * Determine if the user can view the discount_application.
     */
    public function view(User $user, DiscountApplication $discount_application): bool
    {
        return $user->can('view discount_applications');
    }

    /**
     * Determine if the user can create discount_applications.
     */
    public function create(User $user): bool
    {
        return $user->can('create discount_applications');
    }

    /**
     * Determine if the user can update the discount_application.
     */
    public function update(User $user, DiscountApplication $discount_application): bool
    {
        return $user->can('edit discount_applications');
    }

    /**
     * Determine if the user can delete the discount_application.
     */
    public function delete(User $user, DiscountApplication $discount_application): bool
    {
        return $user->can('delete discount_applications');
    }

    /**
     * Determine if the user can restore the discount_application.
     */
    public function restore(User $user, DiscountApplication $discount_application): bool
    {
        return $user->can('delete discount_applications');
    }

    /**
     * Determine if the user can permanently delete the discount_application.
     */
    public function forceDelete(User $user, DiscountApplication $discount_application): bool
    {
        return $user->can('delete discount_applications');
    }
}
