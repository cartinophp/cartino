<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\ProductVariant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ProductVariantRepository extends BaseRepository
{
    protected string $cachePrefix = 'product_variants';

    protected function makeModel(): Model
    {
        return new ProductVariant;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(ProductVariant::class)
            ->allowedFilters([
                AllowedFilter::exact('product_id'),
                AllowedFilter::exact('site_id'),
                'sku',
                'title',
                'status',
            ])
            ->allowedSorts(['sku', 'price', 'created_at', 'title'])
            ->allowedIncludes(['product', 'prices'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?ProductVariant
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): ProductVariant
    {
        $variant = $this->model->create($data);

        $this->clearCache();

        return $variant;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): ProductVariant
    {
        $variant = $this->findOrFail($id);
        $variant->update($data);

        $this->clearCache();

        return $variant->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $variant = $this->findOrFail($id);
        $deleted = $variant->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $variant = $this->findOrFail($id);

        return ! $variant->orderLines()->exists();
    }
}
