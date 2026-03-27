<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AppWebhook;
use Cartino\Models\User;

class AppWebhookPolicy
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
     * Determine if the user can view any app_webhooks.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view app_webhooks');
    }

    /**
     * Determine if the user can view the app_webhook.
     */
    public function view(User $user, AppWebhook $app_webhook): bool
    {
        return $user->can('view app_webhooks');
    }

    /**
     * Determine if the user can create app_webhooks.
     */
    public function create(User $user): bool
    {
        return $user->can('create app_webhooks');
    }

    /**
     * Determine if the user can update the app_webhook.
     */
    public function update(User $user, AppWebhook $app_webhook): bool
    {
        return $user->can('edit app_webhooks');
    }

    /**
     * Determine if the user can delete the app_webhook.
     */
    public function delete(User $user, AppWebhook $app_webhook): bool
    {
        return $user->can('delete app_webhooks');
    }

    /**
     * Determine if the user can restore the app_webhook.
     */
    public function restore(User $user, AppWebhook $app_webhook): bool
    {
        return $user->can('delete app_webhooks');
    }

    /**
     * Determine if the user can permanently delete the app_webhook.
     */
    public function forceDelete(User $user, AppWebhook $app_webhook): bool
    {
        return $user->can('delete app_webhooks');
    }
}
