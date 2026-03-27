<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\ProductReview;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductReviewRepository extends BaseRepository
{
    protected string $cachePrefix = 'product_reviews';

    protected function makeModel(): Model
    {
        return new ProductReview;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(ProductReview::class)
            ->allowedFilters([
                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('customer_id'),
                AllowedFilter::exact('rating'),
                'status',
                AllowedFilter::scope('approved'),
                AllowedFilter::scope('pending'),
            ])
            ->allowedSorts(['rating', 'created_at', 'helpful_count'])
            ->allowedIncludes(['product', 'customer', 'media', 'votes'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?ProductReview
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): ProductReview
    {
        $review = $this->model->create($data);

        $this->clearCache();

        return $review;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): ProductReview
    {
        $review = $this->findOrFail($id);
        $review->update($data);

        $this->clearCache();

        return $review->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $review = $this->findOrFail($id);
        $deleted = $review->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Reviews can always be deleted
    }

    /**
     * Approve review
     */
    public function approveReview(int $id): ProductReview
    {
        $review = $this->findOrFail($id);
        $review->update(['status' => 'approved', 'published_at' => now()]);

        $this->clearCache();

        return $review->fresh();
    }

    /**
     * Reject review
     */
    public function rejectReview(int $id): ProductReview
    {
        $review = $this->findOrFail($id);
        $review->update(['status' => 'rejected']);

        $this->clearCache();

        return $review->fresh();
    }
}
