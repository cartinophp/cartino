<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\FidelityTransaction;
use Cartino\Models\User;

class FidelityTransactionPolicy
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
     * Determine if the user can view any fidelity_transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view fidelity_transactions');
    }

    /**
     * Determine if the user can view the fidelity_transaction.
     */
    public function view(User $user, FidelityTransaction $fidelity_transaction): bool
    {
        return $user->can('view fidelity_transactions');
    }

    /**
     * Determine if the user can create fidelity_transactions.
     */
    public function create(User $user): bool
    {
        return $user->can('create fidelity_transactions');
    }

    /**
     * Determine if the user can update the fidelity_transaction.
     */
    public function update(User $user, FidelityTransaction $fidelity_transaction): bool
    {
        return $user->can('edit fidelity_transactions');
    }

    /**
     * Determine if the user can delete the fidelity_transaction.
     */
    public function delete(User $user, FidelityTransaction $fidelity_transaction): bool
    {
        return $user->can('delete fidelity_transactions');
    }

    /**
     * Determine if the user can restore the fidelity_transaction.
     */
    public function restore(User $user, FidelityTransaction $fidelity_transaction): bool
    {
        return $user->can('delete fidelity_transactions');
    }

    /**
     * Determine if the user can permanently delete the fidelity_transaction.
     */
    public function forceDelete(User $user, FidelityTransaction $fidelity_transaction): bool
    {
        return $user->can('delete fidelity_transactions');
    }
}
