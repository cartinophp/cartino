<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\FidelityCard;
use Cartino\Models\User;

class FidelityCardPolicy
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
     * Determine if the user can view any fidelity_cards.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view fidelity_cards');
    }

    /**
     * Determine if the user can view the fidelity_card.
     */
    public function view(User $user, FidelityCard $fidelity_card): bool
    {
        return $user->can('view fidelity_cards');
    }

    /**
     * Determine if the user can create fidelity_cards.
     */
    public function create(User $user): bool
    {
        return $user->can('create fidelity_cards');
    }

    /**
     * Determine if the user can update the fidelity_card.
     */
    public function update(User $user, FidelityCard $fidelity_card): bool
    {
        return $user->can('edit fidelity_cards');
    }

    /**
     * Determine if the user can delete the fidelity_card.
     */
    public function delete(User $user, FidelityCard $fidelity_card): bool
    {
        return $user->can('delete fidelity_cards');
    }

    /**
     * Determine if the user can restore the fidelity_card.
     */
    public function restore(User $user, FidelityCard $fidelity_card): bool
    {
        return $user->can('delete fidelity_cards');
    }

    /**
     * Determine if the user can permanently delete the fidelity_card.
     */
    public function forceDelete(User $user, FidelityCard $fidelity_card): bool
    {
        return $user->can('delete fidelity_cards');
    }
}
