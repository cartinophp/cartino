<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ProductSupplier;
use Cartino\Models\User;

class ProductSupplierPolicy
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
     * Determine if the user can view any product_suppliers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view product_suppliers');
    }

    /**
     * Determine if the user can view the product_supplier.
     */
    public function view(User $user, ProductSupplier $product_supplier): bool
    {
        return $user->can('view product_suppliers');
    }

    /**
     * Determine if the user can create product_suppliers.
     */
    public function create(User $user): bool
    {
        return $user->can('create product_suppliers');
    }

    /**
     * Determine if the user can update the product_supplier.
     */
    public function update(User $user, ProductSupplier $product_supplier): bool
    {
        return $user->can('edit product_suppliers');
    }

    /**
     * Determine if the user can delete the product_supplier.
     */
    public function delete(User $user, ProductSupplier $product_supplier): bool
    {
        return $user->can('delete product_suppliers');
    }

    /**
     * Determine if the user can restore the product_supplier.
     */
    public function restore(User $user, ProductSupplier $product_supplier): bool
    {
        return $user->can('delete product_suppliers');
    }

    /**
     * Determine if the user can permanently delete the product_supplier.
     */
    public function forceDelete(User $user, ProductSupplier $product_supplier): bool
    {
        return $user->can('delete product_suppliers');
    }
}
