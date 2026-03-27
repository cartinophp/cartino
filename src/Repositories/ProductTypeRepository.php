<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\ProductType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class ProductTypeRepository extends BaseRepository
{
    protected string $cachePrefix = 'product_types';

    protected function makeModel(): Model
    {
        return new ProductType;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(ProductType::class)
            ->allowedFilters([
                'name',
                'slug',
                'status',
            ])
            ->allowedSorts(['name', 'created_at'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $slugOrId): ?ProductType
    {
        return $this->model
            ->where('id', $slugOrId)
            ->orWhere('slug', $slugOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): ProductType
    {
        $productType = $this->model->create($data);

        $this->clearCache();

        return $productType;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): ProductType
    {
        $productType = $this->findOrFail($id);
        $productType->update($data);

        $this->clearCache();

        return $productType->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $productType = $this->findOrFail($id);
        $deleted = $productType->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $productType = $this->findOrFail($id);

        return ! $productType->products()->exists();
    }

    /**
     * Toggle product type status
     */
    public function toggleStatus(int $id): ProductType
    {
        $productType = $this->findOrFail($id);
        $newStatus = $productType->status === 'active' ? 'inactive' : 'active';
        $productType->update(['status' => $newStatus]);

        $this->clearCache();

        return $productType->fresh();
    }
}
