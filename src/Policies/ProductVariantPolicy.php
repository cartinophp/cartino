<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ProductVariant;
use Cartino\Models\User;

class ProductVariantPolicy
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
     * Determine if the user can view any product_variants.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view product_variants');
    }

    /**
     * Determine if the user can view the product_variant.
     */
    public function view(User $user, ProductVariant $product_variant): bool
    {
        return $user->can('view product_variants');
    }

    /**
     * Determine if the user can create product_variants.
     */
    public function create(User $user): bool
    {
        return $user->can('create product_variants');
    }

    /**
     * Determine if the user can update the product_variant.
     */
    public function update(User $user, ProductVariant $product_variant): bool
    {
        return $user->can('edit product_variants');
    }

    /**
     * Determine if the user can delete the product_variant.
     */
    public function delete(User $user, ProductVariant $product_variant): bool
    {
        return $user->can('delete product_variants');
    }

    /**
     * Determine if the user can restore the product_variant.
     */
    public function restore(User $user, ProductVariant $product_variant): bool
    {
        return $user->can('delete product_variants');
    }

    /**
     * Determine if the user can permanently delete the product_variant.
     */
    public function forceDelete(User $user, ProductVariant $product_variant): bool
    {
        return $user->can('delete product_variants');
    }
}
