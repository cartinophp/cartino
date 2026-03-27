<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\Setting;
use Cartino\Models\User;

class SettingPolicy
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
     * Determine if the user can view any settings.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view settings');
    }

    /**
     * Determine if the user can view the setting.
     */
    public function view(User $user, Setting $setting): bool
    {
        return $user->can('view settings');
    }

    /**
     * Determine if the user can create settings.
     */
    public function create(User $user): bool
    {
        return $user->can('create settings');
    }

    /**
     * Determine if the user can update the setting.
     */
    public function update(User $user, Setting $setting): bool
    {
        return $user->can('edit settings');
    }

    /**
     * Determine if the user can delete the setting.
     */
    public function delete(User $user, Setting $setting): bool
    {
        return $user->can('delete settings');
    }

    /**
     * Determine if the user can restore the setting.
     */
    public function restore(User $user, Setting $setting): bool
    {
        return $user->can('delete settings');
    }

    /**
     * Determine if the user can permanently delete the setting.
     */
    public function forceDelete(User $user, Setting $setting): bool
    {
        return $user->can('delete settings');
    }
}
