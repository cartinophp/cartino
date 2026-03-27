<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ReviewMedia;
use Cartino\Models\User;

class ReviewMediaPolicy
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
     * Determine if the user can view any review_medias.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view review_medias');
    }

    /**
     * Determine if the user can view the review_media.
     */
    public function view(User $user, ReviewMedia $review_media): bool
    {
        return $user->can('view review_medias');
    }

    /**
     * Determine if the user can create review_medias.
     */
    public function create(User $user): bool
    {
        return $user->can('create review_medias');
    }

    /**
     * Determine if the user can update the review_media.
     */
    public function update(User $user, ReviewMedia $review_media): bool
    {
        return $user->can('edit review_medias');
    }

    /**
     * Determine if the user can delete the review_media.
     */
    public function delete(User $user, ReviewMedia $review_media): bool
    {
        return $user->can('delete review_medias');
    }

    /**
     * Determine if the user can restore the review_media.
     */
    public function restore(User $user, ReviewMedia $review_media): bool
    {
        return $user->can('delete review_medias');
    }

    /**
     * Determine if the user can permanently delete the review_media.
     */
    public function forceDelete(User $user, ReviewMedia $review_media): bool
    {
        return $user->can('delete review_medias');
    }
}
