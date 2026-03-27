<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Transaction;
use Cartino\Models\User;

class TransactionPolicy
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
     * Determine if the user can view any transactions.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view transactions');
    }

    /**
     * Determine if the user can view the transaction.
     */
    public function view(User $user, Transaction $transaction): bool
    {
        return $user->can('view transactions');
    }

    /**
     * Determine if the user can create transactions.
     */
    public function create(User $user): bool
    {
        return $user->can('create transactions');
    }

    /**
     * Determine if the user can update the transaction.
     */
    public function update(User $user, Transaction $transaction): bool
    {
        return $user->can('edit transactions');
    }

    /**
     * Determine if the user can delete the transaction.
     */
    public function delete(User $user, Transaction $transaction): bool
    {
        return $user->can('delete transactions');
    }

    /**
     * Determine if the user can restore the transaction.
     */
    public function restore(User $user, Transaction $transaction): bool
    {
        return $user->can('delete transactions');
    }

    /**
     * Determine if the user can permanently delete the transaction.
     */
    public function forceDelete(User $user, Transaction $transaction): bool
    {
        return $user->can('delete transactions');
    }
}
