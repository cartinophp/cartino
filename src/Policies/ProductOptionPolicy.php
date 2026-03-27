<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ProductOption;
use Cartino\Models\User;

class ProductOptionPolicy
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
     * Determine if the user can view any product_options.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view product_options');
    }

    /**
     * Determine if the user can view the product_option.
     */
    public function view(User $user, ProductOption $product_option): bool
    {
        return $user->can('view product_options');
    }

    /**
     * Determine if the user can create product_options.
     */
    public function create(User $user): bool
    {
        return $user->can('create product_options');
    }

    /**
     * Determine if the user can update the product_option.
     */
    public function update(User $user, ProductOption $product_option): bool
    {
        return $user->can('edit product_options');
    }

    /**
     * Determine if the user can delete the product_option.
     */
    public function delete(User $user, ProductOption $product_option): bool
    {
        return $user->can('delete product_options');
    }

    /**
     * Determine if the user can restore the product_option.
     */
    public function restore(User $user, ProductOption $product_option): bool
    {
        return $user->can('delete product_options');
    }

    /**
     * Determine if the user can permanently delete the product_option.
     */
    public function forceDelete(User $user, ProductOption $product_option): bool
    {
        return $user->can('delete product_options');
    }
}
