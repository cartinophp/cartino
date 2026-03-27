<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AppApiToken;
use Cartino\Models\User;

class AppApiTokenPolicy
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
     * Determine if the user can view any app_api_tokens.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view app_api_tokens');
    }

    /**
     * Determine if the user can view the app_api_token.
     */
    public function view(User $user, AppApiToken $app_api_token): bool
    {
        return $user->can('view app_api_tokens');
    }

    /**
     * Determine if the user can create app_api_tokens.
     */
    public function create(User $user): bool
    {
        return $user->can('create app_api_tokens');
    }

    /**
     * Determine if the user can update the app_api_token.
     */
    public function update(User $user, AppApiToken $app_api_token): bool
    {
        return $user->can('edit app_api_tokens');
    }

    /**
     * Determine if the user can delete the app_api_token.
     */
    public function delete(User $user, AppApiToken $app_api_token): bool
    {
        return $user->can('delete app_api_tokens');
    }

    /**
     * Determine if the user can restore the app_api_token.
     */
    public function restore(User $user, AppApiToken $app_api_token): bool
    {
        return $user->can('delete app_api_tokens');
    }

    /**
     * Determine if the user can permanently delete the app_api_token.
     */
    public function forceDelete(User $user, AppApiToken $app_api_token): bool
    {
        return $user->can('delete app_api_tokens');
    }
}
