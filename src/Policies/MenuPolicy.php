<?php

namespace Cartino\Policies;

use Cartino\Models\Menu;
use Cartino\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view-menus');
    }

    public function view(User $user, Menu $menu): bool
    {
        return $user->can('view-menus');
    }

    public function create(User $user): bool
    {
        return $user->can('create-menus');
    }

    public function update(User $user, Menu $menu): bool
    {
        return $user->can('edit-menus');
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $user->can('delete-menus');
    }

    public function restore(User $user, Menu $menu): bool
    {
        return $user->can('restore-menus');
    }

    public function forceDelete(User $user, Menu $menu): bool
    {
        return $user->can('force-delete-menus');
    }

    public function reorder(User $user, Menu $menu): bool
    {
        return $user->can('reorder-menu-items');
    }

    public function duplicate(User $user, Menu $menu): bool
    {
        return $user->can('duplicate-menus');
    }
}
