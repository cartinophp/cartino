<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\CustomerGroup;
use Cartino\Models\User;

class CustomerGroupPolicy
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
     * Determine if the user can view any customer_groups.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view customer_groups');
    }

    /**
     * Determine if the user can view the customer_group.
     */
    public function view(User $user, CustomerGroup $customer_group): bool
    {
        return $user->can('view customer_groups');
    }

    /**
     * Determine if the user can create customer_groups.
     */
    public function create(User $user): bool
    {
        return $user->can('create customer_groups');
    }

    /**
     * Determine if the user can update the customer_group.
     */
    public function update(User $user, CustomerGroup $customer_group): bool
    {
        return $user->can('edit customer_groups');
    }

    /**
     * Determine if the user can delete the customer_group.
     */
    public function delete(User $user, CustomerGroup $customer_group): bool
    {
        return $user->can('delete customer_groups');
    }

    /**
     * Determine if the user can restore the customer_group.
     */
    public function restore(User $user, CustomerGroup $customer_group): bool
    {
        return $user->can('delete customer_groups');
    }

    /**
     * Determine if the user can permanently delete the customer_group.
     */
    public function forceDelete(User $user, CustomerGroup $customer_group): bool
    {
        return $user->can('delete customer_groups');
    }
}
