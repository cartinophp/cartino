<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Channel;
use Cartino\Models\User;

class ChannelPolicy
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
     * Determine if the user can view any channels.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view channels');
    }

    /**
     * Determine if the user can view the channel.
     */
    public function view(User $user, Channel $channel): bool
    {
        return $user->can('view channels');
    }

    /**
     * Determine if the user can create channels.
     */
    public function create(User $user): bool
    {
        return $user->can('create channels');
    }

    /**
     * Determine if the user can update the channel.
     */
    public function update(User $user, Channel $channel): bool
    {
        return $user->can('edit channels');
    }

    /**
     * Determine if the user can delete the channel.
     */
    public function delete(User $user, Channel $channel): bool
    {
        return $user->can('delete channels');
    }

    /**
     * Determine if the user can restore the channel.
     */
    public function restore(User $user, Channel $channel): bool
    {
        return $user->can('delete channels');
    }

    /**
     * Determine if the user can permanently delete the channel.
     */
    public function forceDelete(User $user, Channel $channel): bool
    {
        return $user->can('delete channels');
    }
}
