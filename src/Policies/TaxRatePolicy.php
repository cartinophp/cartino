<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\TaxRate;
use Cartino\Models\User;

class TaxRatePolicy
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
     * Determine if the user can view any tax_rates.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view tax_rates');
    }

    /**
     * Determine if the user can view the tax_rate.
     */
    public function view(User $user, TaxRate $tax_rate): bool
    {
        return $user->can('view tax_rates');
    }

    /**
     * Determine if the user can create tax_rates.
     */
    public function create(User $user): bool
    {
        return $user->can('create tax_rates');
    }

    /**
     * Determine if the user can update the tax_rate.
     */
    public function update(User $user, TaxRate $tax_rate): bool
    {
        return $user->can('edit tax_rates');
    }

    /**
     * Determine if the user can delete the tax_rate.
     */
    public function delete(User $user, TaxRate $tax_rate): bool
    {
        return $user->can('delete tax_rates');
    }

    /**
     * Determine if the user can restore the tax_rate.
     */
    public function restore(User $user, TaxRate $tax_rate): bool
    {
        return $user->can('delete tax_rates');
    }

    /**
     * Determine if the user can permanently delete the tax_rate.
     */
    public function forceDelete(User $user, TaxRate $tax_rate): bool
    {
        return $user->can('delete tax_rates');
    }
}
