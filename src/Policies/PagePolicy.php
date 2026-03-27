<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Page;
use Cartino\Models\User;

class PagePolicy
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
     * Determine if the user can view any pages.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view pages');
    }

    /**
     * Determine if the user can view the page.
     */
    public function view(User $user, Page $page): bool
    {
        return $user->can('view pages');
    }

    /**
     * Determine if the user can create pages.
     */
    public function create(User $user): bool
    {
        return $user->can('create pages');
    }

    /**
     * Determine if the user can update the page.
     */
    public function update(User $user, Page $page): bool
    {
        return $user->can('edit pages');
    }

    /**
     * Determine if the user can delete the page.
     */
    public function delete(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }

    /**
     * Determine if the user can restore the page.
     */
    public function restore(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }

    /**
     * Determine if the user can permanently delete the page.
     */
    public function forceDelete(User $user, Page $page): bool
    {
        return $user->can('delete pages');
    }
}
