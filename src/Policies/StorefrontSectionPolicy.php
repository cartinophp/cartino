<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\StorefrontSection;
use Cartino\Models\User;

class StorefrontSectionPolicy
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
     * Determine if the user can view any storefront_sections.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view storefront_sections');
    }

    /**
     * Determine if the user can view the storefront_section.
     */
    public function view(User $user, StorefrontSection $storefront_section): bool
    {
        return $user->can('view storefront_sections');
    }

    /**
     * Determine if the user can create storefront_sections.
     */
    public function create(User $user): bool
    {
        return $user->can('create storefront_sections');
    }

    /**
     * Determine if the user can update the storefront_section.
     */
    public function update(User $user, StorefrontSection $storefront_section): bool
    {
        return $user->can('edit storefront_sections');
    }

    /**
     * Determine if the user can delete the storefront_section.
     */
    public function delete(User $user, StorefrontSection $storefront_section): bool
    {
        return $user->can('delete storefront_sections');
    }

    /**
     * Determine if the user can restore the storefront_section.
     */
    public function restore(User $user, StorefrontSection $storefront_section): bool
    {
        return $user->can('delete storefront_sections');
    }

    /**
     * Determine if the user can permanently delete the storefront_section.
     */
    public function forceDelete(User $user, StorefrontSection $storefront_section): bool
    {
        return $user->can('delete storefront_sections');
    }
}
