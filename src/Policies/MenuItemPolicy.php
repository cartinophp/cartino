<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\MenuItem;
use Cartino\Models\User;

class MenuItemPolicy
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
     * Determine if the user can view any menu_items.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view menu_items');
    }

    /**
     * Determine if the user can view the menu_item.
     */
    public function view(User $user, MenuItem $menu_item): bool
    {
        return $user->can('view menu_items');
    }

    /**
     * Determine if the user can create menu_items.
     */
    public function create(User $user): bool
    {
        return $user->can('create menu_items');
    }

    /**
     * Determine if the user can update the menu_item.
     */
    public function update(User $user, MenuItem $menu_item): bool
    {
        return $user->can('edit menu_items');
    }

    /**
     * Determine if the user can delete the menu_item.
     */
    public function delete(User $user, MenuItem $menu_item): bool
    {
        return $user->can('delete menu_items');
    }

    /**
     * Determine if the user can restore the menu_item.
     */
    public function restore(User $user, MenuItem $menu_item): bool
    {
        return $user->can('delete menu_items');
    }

    /**
     * Determine if the user can permanently delete the menu_item.
     */
    public function forceDelete(User $user, MenuItem $menu_item): bool
    {
        return $user->can('delete menu_items');
    }
}
