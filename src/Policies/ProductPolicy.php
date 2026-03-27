<?php

namespace Cartino\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Gate;

class ProductPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(Authenticatable $user): bool
    {
        return $this->userCan($user, 'view-products');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(Authenticatable $user, $product): bool
    {
        return $this->userCan($user, 'view-products');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(Authenticatable $user): bool
    {
        return $this->userCan($user, 'create-products');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(Authenticatable $user, $product): bool
    {
        return $this->userCan($user, 'edit-products');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(Authenticatable $user, $product): bool
    {
        return $this->userCan($user, 'delete-products');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(Authenticatable $user, $product): bool
    {
        return $this->userCan($user, 'edit-products');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(Authenticatable $user, $product): bool
    {
        return $this->userCan($user, 'delete-products');
    }

    /**
     * Helper method to check if user has permission.
     */
    private function userCan(Authenticatable $user, string $permission): bool
    {
        // Check if user has the method can() (like models with HasPermissions trait)
        if (method_exists($user, 'can')) {
            return $user->can($permission);
        }

        // Fallback to Gate facade
        return Gate::forUser($user)->allows($permission);
    }
}
