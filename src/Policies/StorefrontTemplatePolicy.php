<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\StorefrontTemplate;
use Cartino\Models\User;

class StorefrontTemplatePolicy
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
     * Determine if the user can view any storefront_templates.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view storefront_templates');
    }

    /**
     * Determine if the user can view the storefront_template.
     */
    public function view(User $user, StorefrontTemplate $storefront_template): bool
    {
        return $user->can('view storefront_templates');
    }

    /**
     * Determine if the user can create storefront_templates.
     */
    public function create(User $user): bool
    {
        return $user->can('create storefront_templates');
    }

    /**
     * Determine if the user can update the storefront_template.
     */
    public function update(User $user, StorefrontTemplate $storefront_template): bool
    {
        return $user->can('edit storefront_templates');
    }

    /**
     * Determine if the user can delete the storefront_template.
     */
    public function delete(User $user, StorefrontTemplate $storefront_template): bool
    {
        return $user->can('delete storefront_templates');
    }

    /**
     * Determine if the user can restore the storefront_template.
     */
    public function restore(User $user, StorefrontTemplate $storefront_template): bool
    {
        return $user->can('delete storefront_templates');
    }

    /**
     * Determine if the user can permanently delete the storefront_template.
     */
    public function forceDelete(User $user, StorefrontTemplate $storefront_template): bool
    {
        return $user->can('delete storefront_templates');
    }
}
