<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\CustomerAddress;
use Cartino\Models\User;

class CustomerAddressPolicy
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
     * Determine if the user can view any customer_addresss.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view customer_addresss');
    }

    /**
     * Determine if the user can view the customer_address.
     */
    public function view(User $user, CustomerAddress $customer_address): bool
    {
        return $user->can('view customer_addresss');
    }

    /**
     * Determine if the user can create customer_addresss.
     */
    public function create(User $user): bool
    {
        return $user->can('create customer_addresss');
    }

    /**
     * Determine if the user can update the customer_address.
     */
    public function update(User $user, CustomerAddress $customer_address): bool
    {
        return $user->can('edit customer_addresss');
    }

    /**
     * Determine if the user can delete the customer_address.
     */
    public function delete(User $user, CustomerAddress $customer_address): bool
    {
        return $user->can('delete customer_addresss');
    }

    /**
     * Determine if the user can restore the customer_address.
     */
    public function restore(User $user, CustomerAddress $customer_address): bool
    {
        return $user->can('delete customer_addresss');
    }

    /**
     * Determine if the user can permanently delete the customer_address.
     */
    public function forceDelete(User $user, CustomerAddress $customer_address): bool
    {
        return $user->can('delete customer_addresss');
    }
}
