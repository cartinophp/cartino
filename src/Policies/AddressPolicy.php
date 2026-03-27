<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Address;
use Cartino\Models\User;

class AddressPolicy
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
     * Determine if the user can view any addresss.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view addresss');
    }

    /**
     * Determine if the user can view the address.
     */
    public function view(User $user, Address $address): bool
    {
        return $user->can('view addresss');
    }

    /**
     * Determine if the user can create addresss.
     */
    public function create(User $user): bool
    {
        return $user->can('create addresss');
    }

    /**
     * Determine if the user can update the address.
     */
    public function update(User $user, Address $address): bool
    {
        return $user->can('edit addresss');
    }

    /**
     * Determine if the user can delete the address.
     */
    public function delete(User $user, Address $address): bool
    {
        return $user->can('delete addresss');
    }

    /**
     * Determine if the user can restore the address.
     */
    public function restore(User $user, Address $address): bool
    {
        return $user->can('delete addresss');
    }

    /**
     * Determine if the user can permanently delete the address.
     */
    public function forceDelete(User $user, Address $address): bool
    {
        return $user->can('delete addresss');
    }
}
