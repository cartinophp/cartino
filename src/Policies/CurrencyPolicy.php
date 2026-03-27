<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Currency;
use Cartino\Models\User;

class CurrencyPolicy
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
     * Determine if the user can view any currencys.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view currencys');
    }

    /**
     * Determine if the user can view the currency.
     */
    public function view(User $user, Currency $currency): bool
    {
        return $user->can('view currencys');
    }

    /**
     * Determine if the user can create currencys.
     */
    public function create(User $user): bool
    {
        return $user->can('create currencys');
    }

    /**
     * Determine if the user can update the currency.
     */
    public function update(User $user, Currency $currency): bool
    {
        return $user->can('edit currencys');
    }

    /**
     * Determine if the user can delete the currency.
     */
    public function delete(User $user, Currency $currency): bool
    {
        return $user->can('delete currencys');
    }

    /**
     * Determine if the user can restore the currency.
     */
    public function restore(User $user, Currency $currency): bool
    {
        return $user->can('delete currencys');
    }

    /**
     * Determine if the user can permanently delete the currency.
     */
    public function forceDelete(User $user, Currency $currency): bool
    {
        return $user->can('delete currencys');
    }
}
