<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Courier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Spatie\QueryBuilder\QueryBuilder;

class CourierRepository extends BaseRepository
{
    protected string $cachePrefix = 'couriers';

    protected function makeModel(): Model
    {
        return new Courier;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return $query = QueryBuilder::for(Courier::class)
            ->allowedFilters(['name', 'slug', 'code', 'status', 'is_enabled'])
            ->allowedSorts(['name', 'code', 'created_at', 'status'])
            ->allowedIncludes(['orders'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Find one by ID, slug or code
     */
    public function findOne(int|string $handle): ?Courier
    {
        return $this->model
            ->where('id', $handle)
            ->orWhere('slug', $handle)
            ->orWhere('code', $handle)
            ->firstOrfail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Courier
    {
        $courier = $this->model->create($data);
        $this->clearCache();

        return $courier;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Courier
    {
        $courier = $this->findOrFail($id);
        $courier->update($data);
        $this->clearCache();

        return $courier->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $courier = $this->findOrFail($id);
        $deleted = $courier->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $courier = $this->findOrFail($id);

        return ! $courier->orders()->exists();
    }

    /**
     * Toggle courier status
     */
    public function toggleStatus(int $id): Courier
    {
        $courier = $this->findOrFail($id);
        $newStatus = $courier->status === 'active' ? 'inactive' : 'active';
        $courier->update(['status' => $newStatus]);
        $this->clearCache();

        return $courier->fresh();
    }

    /**
     * Toggle courier enabled status
     */
    public function toggleEnabled(int $id): Courier
    {
        $courier = $this->findOrFail($id);
        $courier->update(['is_enabled' => ! $courier->is_enabled]);
        $this->clearCache();

        return $courier->fresh();
    }

    /**
     * Create many couriers
     */
    public function createMany(array $dataArray): Collection
    {
        $couriers = collect();

        foreach ($dataArray as $data) {
            $couriers->push($this->model->create($data));
        }

        $this->clearCache();

        return $couriers;
    }

    /**
     * Update many couriers
     */
    public function updateMany(array $ids, array $data): int
    {
        $updated = $this->model->whereIn('id', $ids)->update($data);

        $this->clearCache();

        return $updated;
    }

    /**
     * Delete many couriers
     */
    public function deleteMany(array $ids): int
    {
        $deleted = $this->model->whereIn('id', $ids)->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Find couriers by IDs
     */
    public function findByIds(array $ids): Collection
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    /**
     * Get courier orders
     */
    public function getCourierOrders(int $courierId, array $filters = []): LengthAwarePaginator
    {
        $courier = $this->findOrFail($courierId);

        return QueryBuilder::for($courier->orders()->getQuery())
            ->allowedFilters(['status', 'created_at'])
            ->allowedSorts(['created_at', 'status'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Get enabled couriers only
     */
    public function getEnabled(): Collection
    {
        return $this->model
            ->where('is_enabled', true)
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }
}
