<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\StockNotification;
use Cartino\Models\User;

class StockNotificationPolicy
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
     * Determine if the user can view any stock_notifications.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view stock_notifications');
    }

    /**
     * Determine if the user can view the stock_notification.
     */
    public function view(User $user, StockNotification $stock_notification): bool
    {
        return $user->can('view stock_notifications');
    }

    /**
     * Determine if the user can create stock_notifications.
     */
    public function create(User $user): bool
    {
        return $user->can('create stock_notifications');
    }

    /**
     * Determine if the user can update the stock_notification.
     */
    public function update(User $user, StockNotification $stock_notification): bool
    {
        return $user->can('edit stock_notifications');
    }

    /**
     * Determine if the user can delete the stock_notification.
     */
    public function delete(User $user, StockNotification $stock_notification): bool
    {
        return $user->can('delete stock_notifications');
    }

    /**
     * Determine if the user can restore the stock_notification.
     */
    public function restore(User $user, StockNotification $stock_notification): bool
    {
        return $user->can('delete stock_notifications');
    }

    /**
     * Determine if the user can permanently delete the stock_notification.
     */
    public function forceDelete(User $user, StockNotification $stock_notification): bool
    {
        return $user->can('delete stock_notifications');
    }
}
