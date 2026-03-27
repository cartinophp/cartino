<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SupplierRepository extends BaseRepository
{
    protected string $cachePrefix = 'suppliers';

    protected array $with = ['site'];

    protected function makeModel(): Model
    {
        return new Supplier;
    }

    /**
     * Get paginated suppliers with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Supplier::class)
            ->allowedFilters([
                'name',
                'code',
                'email',
                'status',
                AllowedFilter::exact('country_code'),
                AllowedFilter::exact('is_preferred'),
            ])
            ->allowedSorts(['name', 'code', 'created_at', 'rating'])
            ->allowedIncludes(['site', 'purchaseOrders'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or code
     */
    public function findOne(int|string $codeOrId): ?Supplier
    {
        return $this->model
            ->where('id', $codeOrId)
            ->orWhere('code', $codeOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Supplier
    {
        $supplier = $this->model->create($data);
        $this->clearCache();

        return $supplier;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Supplier
    {
        $supplier = $this->findOrFail($id);
        $supplier->update($data);
        $this->clearCache();

        return $supplier->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $supplier = $this->findOrFail($id);
        $deleted = $supplier->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $supplier = $this->findOrFail($id);

        return ! $supplier->purchaseOrders()->exists();
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(int $id): Supplier
    {
        $supplier = $this->findOrFail($id);
        $newStatus = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->update(['status' => $newStatus]);
        $this->clearCache();

        return $supplier->fresh();
    }

    /**
     * Find supplier by code
     */
    public function findByCode(string $code): ?Supplier
    {
        $cacheKey = $this->getCacheKey('code', $code);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($code) {
            return $this->model->where('code', $code)->first();
        });
    }

    /**
     * Get active suppliers
     */
    public function getActive(): Supplier
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->active()
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get preferred suppliers
     */
    public function getPreferred(): Supplier
    {
        $cacheKey = $this->getCacheKey('preferred', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model
                ->preferred()
                ->orderBy('rating', 'desc')
                ->get();
        });
    }

    /**
     * Update supplier rating
     */
    public function updateRating(int $id, float $rating): bool
    {
        $result = $this->model->where('id', $id)->update(['rating' => $rating]);
        $this->clearCache();

        return (bool) $result;
    }

    /**
     * Get supplier with products
     */
    public function getWithProducts(int $id): ?Supplier
    {
        return $this->model->with(['products', 'productSuppliers.product'])->find($id);
    }

    /**
     * Get supplier with purchase orders
     */
    public function getWithPurchaseOrders(int $id): ?Supplier
    {
        return $this->model->with(['purchaseOrders', 'purchaseOrders.items'])->find($id);
    }

    /**
     * Bulk update supplier status
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['status' => $status]);
        $this->clearCache();

        return $updated;
    }
}
