<?php

namespace Cartino\Policies;

use Cartino\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view-categories');
    }

    public function view(User $user, Category $category): bool
    {
        return $user->can('view-categories');
    }

    public function create(User $user): bool
    {
        return $user->can('create-categories');
    }

    public function update(User $user, Category $category): bool
    {
        return $user->can('edit-categories');
    }

    public function delete(User $user, Category $category): bool
    {
        return $user->can('delete-categories') && ! $category->hasProducts();
    }

    public function restore(User $user, Category $category): bool
    {
        return $user->can('restore-categories');
    }

    public function forceDelete(User $user, Category $category): bool
    {
        return $user->can('force-delete-categories');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder-categories');
    }
}
