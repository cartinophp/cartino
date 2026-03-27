<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\ShippingZone;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class ShippingZoneRepository extends BaseRepository
{
    protected string $cachePrefix = 'shipping_zones';

    protected function makeModel(): Model
    {
        return new ShippingZone;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(ShippingZone::class)
            ->allowedFilters([
                'name',
                AllowedFilter::exact('site_id'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->allowedIncludes(['site', 'rates'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID
     */
    public function findOne(int $id): ?ShippingZone
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create one
     */
    public function createOne(array $data): ShippingZone
    {
        $zone = $this->model->create($data);

        $this->clearCache();

        return $zone;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): ShippingZone
    {
        $zone = $this->findOrFail($id);
        $zone->update($data);

        $this->clearCache();

        return $zone->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $zone = $this->findOrFail($id);
        $deleted = $zone->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        return true; // Shipping zones can always be deleted
    }
}
