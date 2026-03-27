<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Auth\ApiKeyUser;
use Cartino\Models\ApiKey;
use Cartino\Models\User;

class ApiKeyPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super admins can do anything.
     */
    public function before(User|ApiKeyUser $user, string $ability): ?bool
    {
        if ($user instanceof ApiKeyUser) {
            return true; // static API key o ApiKeyUser full_access => super
        }

        if ($user->is_super) {
            return true;
        }

        return null;
    }

    /**
     * Determine if the user can view any API keys.
     */
    public function viewAny(User|ApiKeyUser $user): bool
    {
        return $user->can('view api_keys');
    }

    /**
     * Determine if the user can view the API key.
     */
    public function view(User|ApiKeyUser $user, ApiKey $apiKey): bool
    {
        return $user->can('view api_keys');
    }

    /**
     * Determine if the user can create API keys.
     */
    public function create(User|ApiKeyUser $user): bool
    {
        return $user->can('create api_keys');
    }

    /**
     * Determine if the user can update the API key.
     */
    public function update(User|ApiKeyUser $user, ApiKey $apiKey): bool
    {
        return $user->can('edit api_keys');
    }

    /**
     * Determine if the user can delete the API key.
     */
    public function delete(User|ApiKeyUser $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }

    /**
     * Determine if the user can restore the API key.
     */
    public function restore(User|ApiKeyUser $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }

    /**
     * Determine if the user can permanently delete the API key.
     */
    public function forceDelete(User|ApiKeyUser $user, ApiKey $apiKey): bool
    {
        return $user->can('delete api_keys');
    }
}
