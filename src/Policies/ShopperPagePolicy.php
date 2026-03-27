<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ShopperPage;
use Cartino\Models\User;

class ShopperPagePolicy
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
     * Determine if the user can view any shopper_pages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view shopper_pages');
    }

    /**
     * Determine if the user can view the shopper_page.
     */
    public function view(User $user, ShopperPage $shopper_page): bool
    {
        return $user->can('view shopper_pages');
    }

    /**
     * Determine if the user can create shopper_pages.
     */
    public function create(User $user): bool
    {
        return $user->can('create shopper_pages');
    }

    /**
     * Determine if the user can update the shopper_page.
     */
    public function update(User $user, ShopperPage $shopper_page): bool
    {
        return $user->can('edit shopper_pages');
    }

    /**
     * Determine if the user can delete the shopper_page.
     */
    public function delete(User $user, ShopperPage $shopper_page): bool
    {
        return $user->can('delete shopper_pages');
    }

    /**
     * Determine if the user can restore the shopper_page.
     */
    public function restore(User $user, ShopperPage $shopper_page): bool
    {
        return $user->can('delete shopper_pages');
    }

    /**
     * Determine if the user can permanently delete the shopper_page.
     */
    public function forceDelete(User $user, ShopperPage $shopper_page): bool
    {
        return $user->can('delete shopper_pages');
    }
}
