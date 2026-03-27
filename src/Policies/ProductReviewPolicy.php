<?php

declare(strict_types=1);

namespace Cartino\Policies;

use Cartino\Models\ProductReview;
use Cartino\Models\User;

class ProductReviewPolicy
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
     * Determine if the user can view any product_reviews.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view product_reviews');
    }

    /**
     * Determine if the user can view the product_review.
     */
    public function view(User $user, ProductReview $product_review): bool
    {
        return $user->can('view product_reviews');
    }

    /**
     * Determine if the user can create product_reviews.
     */
    public function create(User $user): bool
    {
        return $user->can('create product_reviews');
    }

    /**
     * Determine if the user can update the product_review.
     */
    public function update(User $user, ProductReview $product_review): bool
    {
        return $user->can('edit product_reviews');
    }

    /**
     * Determine if the user can delete the product_review.
     */
    public function delete(User $user, ProductReview $product_review): bool
    {
        return $user->can('delete product_reviews');
    }

    /**
     * Determine if the user can restore the product_review.
     */
    public function restore(User $user, ProductReview $product_review): bool
    {
        return $user->can('delete product_reviews');
    }

    /**
     * Determine if the user can permanently delete the product_review.
     */
    public function forceDelete(User $user, ProductReview $product_review): bool
    {
        return $user->can('delete product_reviews');
    }
}
