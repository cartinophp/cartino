<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\AppReview;
use Cartino\Models\User;

class AppReviewPolicy
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
     * Determine if the user can view any app_reviews.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view app_reviews');
    }

    /**
     * Determine if the user can view the app_review.
     */
    public function view(User $user, AppReview $app_review): bool
    {
        return $user->can('view app_reviews');
    }

    /**
     * Determine if the user can create app_reviews.
     */
    public function create(User $user): bool
    {
        return $user->can('create app_reviews');
    }

    /**
     * Determine if the user can update the app_review.
     */
    public function update(User $user, AppReview $app_review): bool
    {
        return $user->can('edit app_reviews');
    }

    /**
     * Determine if the user can delete the app_review.
     */
    public function delete(User $user, AppReview $app_review): bool
    {
        return $user->can('delete app_reviews');
    }

    /**
     * Determine if the user can restore the app_review.
     */
    public function restore(User $user, AppReview $app_review): bool
    {
        return $user->can('delete app_reviews');
    }

    /**
     * Determine if the user can permanently delete the app_review.
     */
    public function forceDelete(User $user, AppReview $app_review): bool
    {
        return $user->can('delete app_reviews');
    }
}
