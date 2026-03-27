<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ProductType;
use Cartino\Models\User;

class ProductTypePolicy
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
     * Determine if the user can view any product_types.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view product_types');
    }

    /**
     * Determine if the user can view the product_type.
     */
    public function view(User $user, ProductType $product_type): bool
    {
        return $user->can('view product_types');
    }

    /**
     * Determine if the user can create product_types.
     */
    public function create(User $user): bool
    {
        return $user->can('create product_types');
    }

    /**
     * Determine if the user can update the product_type.
     */
    public function update(User $user, ProductType $product_type): bool
    {
        return $user->can('edit product_types');
    }

    /**
     * Determine if the user can delete the product_type.
     */
    public function delete(User $user, ProductType $product_type): bool
    {
        return $user->can('delete product_types');
    }

    /**
     * Determine if the user can restore the product_type.
     */
    public function restore(User $user, ProductType $product_type): bool
    {
        return $user->can('delete product_types');
    }

    /**
     * Determine if the user can permanently delete the product_type.
     */
    public function forceDelete(User $user, ProductType $product_type): bool
    {
        return $user->can('delete product_types');
    }
}
