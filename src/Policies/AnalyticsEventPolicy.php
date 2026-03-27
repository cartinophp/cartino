<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AnalyticsEvent;
use Cartino\Models\User;

class AnalyticsEventPolicy
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
     * Determine if the user can view any analytics_events.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view analytics_events');
    }

    /**
     * Determine if the user can view the analytics_event.
     */
    public function view(User $user, AnalyticsEvent $analytics_event): bool
    {
        return $user->can('view analytics_events');
    }

    /**
     * Determine if the user can create analytics_events.
     */
    public function create(User $user): bool
    {
        return $user->can('create analytics_events');
    }

    /**
     * Determine if the user can update the analytics_event.
     */
    public function update(User $user, AnalyticsEvent $analytics_event): bool
    {
        return $user->can('edit analytics_events');
    }

    /**
     * Determine if the user can delete the analytics_event.
     */
    public function delete(User $user, AnalyticsEvent $analytics_event): bool
    {
        return $user->can('delete analytics_events');
    }

    /**
     * Determine if the user can restore the analytics_event.
     */
    public function restore(User $user, AnalyticsEvent $analytics_event): bool
    {
        return $user->can('delete analytics_events');
    }

    /**
     * Determine if the user can permanently delete the analytics_event.
     */
    public function forceDelete(User $user, AnalyticsEvent $analytics_event): bool
    {
        return $user->can('delete analytics_events');
    }
}
