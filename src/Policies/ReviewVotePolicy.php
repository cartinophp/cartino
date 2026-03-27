<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ReviewVote;
use Cartino\Models\User;

class ReviewVotePolicy
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
     * Determine if the user can view any review_votes.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view review_votes');
    }

    /**
     * Determine if the user can view the review_vote.
     */
    public function view(User $user, ReviewVote $review_vote): bool
    {
        return $user->can('view review_votes');
    }

    /**
     * Determine if the user can create review_votes.
     */
    public function create(User $user): bool
    {
        return $user->can('create review_votes');
    }

    /**
     * Determine if the user can update the review_vote.
     */
    public function update(User $user, ReviewVote $review_vote): bool
    {
        return $user->can('edit review_votes');
    }

    /**
     * Determine if the user can delete the review_vote.
     */
    public function delete(User $user, ReviewVote $review_vote): bool
    {
        return $user->can('delete review_votes');
    }

    /**
     * Determine if the user can restore the review_vote.
     */
    public function restore(User $user, ReviewVote $review_vote): bool
    {
        return $user->can('delete review_votes');
    }

    /**
     * Determine if the user can permanently delete the review_vote.
     */
    public function forceDelete(User $user, ReviewVote $review_vote): bool
    {
        return $user->can('delete review_votes');
    }
}
