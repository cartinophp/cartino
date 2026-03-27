<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Country;
use Cartino\Models\User;

class CountryPolicy
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
     * Determine if the user can view any countrys.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view countrys');
    }

    /**
     * Determine if the user can view the country.
     */
    public function view(User $user, Country $country): bool
    {
        return $user->can('view countrys');
    }

    /**
     * Determine if the user can create countrys.
     */
    public function create(User $user): bool
    {
        return $user->can('create countrys');
    }

    /**
     * Determine if the user can update the country.
     */
    public function update(User $user, Country $country): bool
    {
        return $user->can('edit countrys');
    }

    /**
     * Determine if the user can delete the country.
     */
    public function delete(User $user, Country $country): bool
    {
        return $user->can('delete countrys');
    }

    /**
     * Determine if the user can restore the country.
     */
    public function restore(User $user, Country $country): bool
    {
        return $user->can('delete countrys');
    }

    /**
     * Determine if the user can permanently delete the country.
     */
    public function forceDelete(User $user, Country $country): bool
    {
        return $user->can('delete countrys');
    }
}
