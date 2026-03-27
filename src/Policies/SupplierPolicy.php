<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Supplier;
use Cartino\Models\User;

class SupplierPolicy
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
     * Determine if the user can view any suppliers.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view suppliers');
    }

    /**
     * Determine if the user can view the supplier.
     */
    public function view(User $user, Supplier $supplier): bool
    {
        return $user->can('view suppliers');
    }

    /**
     * Determine if the user can create suppliers.
     */
    public function create(User $user): bool
    {
        return $user->can('create suppliers');
    }

    /**
     * Determine if the user can update the supplier.
     */
    public function update(User $user, Supplier $supplier): bool
    {
        return $user->can('edit suppliers');
    }

    /**
     * Determine if the user can delete the supplier.
     */
    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->can('delete suppliers');
    }

    /**
     * Determine if the user can restore the supplier.
     */
    public function restore(User $user, Supplier $supplier): bool
    {
        return $user->can('delete suppliers');
    }

    /**
     * Determine if the user can permanently delete the supplier.
     */
    public function forceDelete(User $user, Supplier $supplier): bool
    {
        return $user->can('delete suppliers');
    }
}
